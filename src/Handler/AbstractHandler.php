<?php

namespace PHPWhois2\Handler;

use PHPWhois2\Data;
use PHPWhois2\WhoisClient;

abstract class AbstractHandler
{
    public function __construct(protected readonly WhoisClient $whoisClient)
    {
    }

    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = static::class;

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData);
        $data->regyinfo = [];
        $data->rawData = $rawData;

        return $data;
    }

    protected function easyParser(array $rawData, array $items, string $dateFormat, array $translate = [], bool $hasOrg = false, bool $partialMatch = false, bool $defBlock = false): array
    {
        $r = $this->getBlocks($rawData, $items, $partialMatch, $defBlock);
        $r = $this->getContacts($r, $translate, $hasOrg);
        $this->formatDates($r, $dateFormat);

        return $r;
    }

    protected function formatDates(&$res, string $format = 'mdy'): array
    {
        if (!\is_array($res)) {
            return $res;
        }

        foreach ($res as $key => $val) {
            $key_to_ignore = (!\is_numeric($key) && ('expires' === $key || 'created' === $key || 'changed' === $key));

            if (\is_array($val)) {
                if ($key_to_ignore) {
                    $d = $this->getDate($val[0], $format);
                    if ($d) {
                        $res[$key] = $d;
                    }
                } else {
                    $res[$key] = $this->formatDates($val, $format);
                }
            } elseif ($key_to_ignore) {
                $d = $this->getDate($val, $format);
                if ($d) {
                    $res[$key] = $d;
                }
            }
        }

        return $res;
    }

    protected function generic_parser_a(array $rawData, array $translate, array $contacts, string $main = 'domain', string $dateFormat = 'dmy'): array
    {
        $disclaimer = [];
        $blocks = $this->generic_parser_a_blocks($rawData, $translate, $disclaimer);

        $ret = [];
        if (isset($disclaimer) && \is_array($disclaimer)) {
            $ret['disclaimer'] = $disclaimer;
        }

        if (empty($blocks) || !\is_array($blocks['main'])) {
            $ret['registered'] = 'no';

            return $ret;
        }

        $r = $blocks['main'];
        $ret['registered'] = 'yes';

        foreach ($contacts as $key => $val) {
            if (isset($r[$key])) {
                if (\is_array($r[$key])) {
                    $blk = $r[$key][\count($r[$key]) - 1];
                } else {
                    $blk = $r[$key];
                }

                $blk = \strtoupper(\strtok($blk, ' '));
                if (isset($blocks[$blk])) {
                    $ret[$val] = $blocks[$blk];
                }
                unset($r[$key]);
            }
        }

        if ($main) {
            $ret[$main] = $r;
        }

        $this->formatDates($ret, $dateFormat);

        return $ret;
    }

    protected function generic_parser_a_blocks(array $rawData, array $translate, array &$disclaimer = []): array
    {
        $newblock = false;
        $hasdata = false;
        $block = [];
        $blocks = [];
        $gkey = 'main';
        $dend = false;

        foreach ($rawData as $val) {
            $val = \trim($val);

            if ('' !== $val && ('%' === $val[0] || '#' === $val[0])) {
                if (!$dend) {
                    $disclaimer[] = \trim(\substr($val, 1));
                }
                continue;
            }
            if ('' === $val) {
                $newblock = true;
                continue;
            }
            if ($newblock && $hasdata) {
                $blocks[$gkey] = $block;
                $block = [];
                $gkey = '';
            }
            $dend = true;
            $newblock = false;
            $k = \trim(\strtok($val, ':'));
            $v = \trim(\substr(\strstr($val, ':'), 1));

            if ('' === $v) {
                continue;
            }

            $hasdata = true;

            if (isset($translate[$k])) {
                $k = $translate[$k];
                if ('' === $k) {
                    continue;
                }
                if (\str_contains($k, '.')) {
                    $block = self::assign($block, $k, $v);
                    continue;
                }
            } else {
                $k = \strtolower($k);
            }

            if ('handle' === $k) {
                $v = \strtok($v, ' ');
                $gkey = \strtoupper($v);
            }

            if (isset($block[$k]) && \is_array($block[$k])) {
                $block[$k][] = $v;
            } elseif (empty($block[$k])) {
                $block[$k] = $v;
            } else {
                $x = $block[$k];
                unset($block[$k]);
                $block[$k][] = $x;
                $block[$k][] = $v;
            }
        }

        if ($hasdata) {
            $blocks[$gkey] = $block;
        }

        return $blocks;
    }

    protected function generic_parser_b(array $rawData, array $items = [], string $dateFormat = 'mdy', bool $hasReg = true, bool $scanAll = false): array
    {
        if (!$items) {
            $items = [
                'Registrant:' => 'owner.',
                'domain:' => 'domain.name',
                'Domain Name:' => 'domain.name',
                'Domain:' => 'domain.name',
                'Domain ID:' => 'domain.handle',
                'Domain ROID:' => 'domain.handle',
                'Sponsoring Registrar:' => 'domain.sponsor',
                'Registrar:' => 'domain.sponsor',
                'Registrar ID:' => 'domain.sponsor',
                'Domain Status:' => 'domain.status.',
                'Status:' => 'domain.status.',
                'nserver:' => 'domain.nserver.',
                'DNS Servers:' => 'domain.nserver.',
                'Name Server:' => 'domain.nserver.',
                'Nameservers:' => 'domain.nserver.',
                'Nserver:' => 'domain.nserver.',
                'Domain servers in listed order:' => 'domain.nserver.',
                'Maintainer:' => 'domain.referer',
                'created:' => 'domain.created',
                'Created on..............:' => 'domain.created',
                'Record created on:' => 'domain.created',
                'Registration Date:' => 'domain.created',
                'Domain Registration Date:' => 'domain.created',
                'Domain Create Date:' => 'domain.created',
                'Domain created on:' => 'domain.created',
                'Expires on..............:' => 'domain.expires',
                'Record expires on:' => 'domain.expires',
                'Domain Expiration Date:' => 'domain.expires',
                'changed:' => 'domain.changed',
                'Last modified on........:' => 'domain.changed',
                'Domain Last Updated Date:' => 'domain.changed',
                'Updated Date:' => 'domain.changed',
                'Creation Date:' => 'domain.created',
                'Last Modification Date:' => 'domain.changed',
                'Expiration Date:' => 'domain.expires',
                'Domain expires on:' => 'domain.expires',
                'Created On:' => 'domain.created',
                'Last Updated On:' => 'domain.changed',
                'Last Updated on:' => 'domain.changed',
                'Last updated on:' => 'domain.changed',
                'Record last updated:' => 'domain.changed',
                'Renewal Date:' => 'domain.expires',
                'Registry Expiry Date:' => 'domain.expires',
                'source:' => 'domain.source',
                'Registrant ID:' => 'owner.handle',
                'Domain Holder:' => 'owner.name',
                'Registrant Name:' => 'owner.name',
                'Registrant Organization:' => 'owner.organization',
                'Registrant Address:' => 'owner.address.street.',
                'Registrant Address1:' => 'owner.address.street.',
                'Registrant Address2:' => 'owner.address.street.',
                'Registrant Street:' => 'owner.address.street.',
                'Registrant Street1:' => 'owner.address.street.',
                'Registrant Street2:' => 'owner.address.street.',
                'Registrant Street3:' => 'owner.address.street.',
                'Registrant Postal Code:' => 'owner.address.pcode',
                'Registrant City:' => 'owner.address.city',
                'Registrant State/Province:' => 'owner.address.state',
                'Registrant Country:' => 'owner.address.country',
                'Registrant Country Code:' => 'owner.address.country',
                'Registrant Country/Economy:' => 'owner.address.country',
                'Registrant Phone Number:' => 'owner.phone',
                'Registrant Phone:' => 'owner.phone',
                'Registrant Facsimile Number:' => 'owner.fax',
                'Registrant FAX:' => 'owner.fax',
                'Registrant Email:' => 'owner.email',
                'Registrant E-mail:' => 'owner.email',
                'Registrant Contact ID:' => 'owner.handle',
                'Registrant Contact Name:' => 'owner.name',
                'Registrant Contact Organisation:' => 'owner.organization',
                'Admin-c:' => 'admin.handle',
                'Administrative Contact ID:' => 'admin.handle',
                'Administrative Contact Name:' => 'admin.name',
                'Administrative Contact Organization:' => 'admin.organization',
                'Administrative Contact Address:' => 'admin.address.street.',
                'Administrative Contact Address1:' => 'admin.address.street.',
                'Administrative Contact Address2:' => 'admin.address.street.',
                'Administrative Contact Postal Code:' => 'admin.address.pcode',
                'Administrative Contact City:' => 'admin.address.city',
                'Administrative Contact State/Province:' => 'admin.address.state',
                'Administrative Contact Country:' => 'admin.address.country',
                'Administrative Contact Country Code:' => 'admin.address.country',
                'Administrative Contact Phone Number:' => 'admin.phone',
                'Administrative Contact Email:' => 'admin.email',
                'Administrative Contact Facsimile Number:' => 'admin.fax',
                'Administrative Contact Tel:' => 'admin.phone',
                'Administrative Contact Fax:' => 'admin.fax',
                'Administrative ID:' => 'admin.handle',
                'Administrative Name:' => 'admin.name',
                'Administrative Organization:' => 'admin.organization',
                'Administrative Address:' => 'admin.address.street.',
                'Administrative Address1:' => 'admin.address.street.',
                'Administrative Address2:' => 'admin.address.street.',
                'Administrative Postal Code:' => 'admin.address.pcode',
                'Administrative City:' => 'admin.address.city',
                'Administrative State/Province:' => 'admin.address.state',
                'Administrative Country/Economy:' => 'admin.address.country',
                'Administrative Phone:' => 'admin.phone',
                'Administrative E-mail:' => 'admin.email',
                'Administrative Facsimile Number:' => 'admin.fax',
                'Administrative Tel:' => 'admin.phone',
                'Administrative FAX:' => 'admin.fax',
                'Admin ID:' => 'admin.handle',
                'Admin Name:' => 'admin.name',
                'Admin Organization:' => 'admin.organization',
                'Admin Street:' => 'admin.address.street.',
                'Admin Street1:' => 'admin.address.street.',
                'Admin Street2:' => 'admin.address.street.',
                'Admin Street3:' => 'admin.address.street.',
                'Admin Address:' => 'admin.address.street.',
                'Admin Address2:' => 'admin.address.street.',
                'Admin Address3:' => 'admin.address.street.',
                'Admin City:' => 'admin.address.city',
                'Admin State/Province:' => 'admin.address.state',
                'Admin Postal Code:' => 'admin.address.pcode',
                'Admin Country:' => 'admin.address.country',
                'Admin Country/Economy:' => 'admin.address.country',
                'Admin Phone:' => 'admin.phone',
                'Admin FAX:' => 'admin.fax',
                'Admin Email:' => 'admin.email',
                'Admin E-mail:' => 'admin.email',
                'Tech-c:' => 'tech.handle',
                'Technical Contact ID:' => 'tech.handle',
                'Technical Contact Name:' => 'tech.name',
                'Technical Contact Organization:' => 'tech.organization',
                'Technical Contact Address:' => 'tech.address.street.',
                'Technical Contact Address1:' => 'tech.address.street.',
                'Technical Contact Address2:' => 'tech.address.street.',
                'Technical Contact Postal Code:' => 'tech.address.pcode',
                'Technical Contact City:' => 'tech.address.city',
                'Technical Contact State/Province:' => 'tech.address.state',
                'Technical Contact Country:' => 'tech.address.country',
                'Technical Contact Phone Number:' => 'tech.phone',
                'Technical Contact Facsimile Number:' => 'tech.fax',
                'Technical Contact Phone:' => 'tech.phone',
                'Technical Contact Fax:' => 'tech.fax',
                'Technical Contact Email:' => 'tech.email',
                'Technical ID:' => 'tech.handle',
                'Technical Name:' => 'tech.name',
                'Technical Organization:' => 'tech.organization',
                'Technical Address:' => 'tech.address.street.',
                'Technical Address1:' => 'tech.address.street.',
                'Technical Address2:' => 'tech.address.street.',
                'Technical Postal Code:' => 'tech.address.pcode',
                'Technical City:' => 'tech.address.city',
                'Technical State/Province:' => 'tech.address.state',
                'Technical Country/Economy:' => 'tech.address.country',
                'Technical Phone Number:' => 'tech.phone',
                'Technical Facsimile Number:' => 'tech.fax',
                'Technical Phone:' => 'tech.phone',
                'Technical Fax:' => 'tech.fax',
                'Technical FAX:' => 'tech.fax',
                'Technical E-mail:' => 'tech.email',
                'Technical Email:' => 'tech.email',
                'Tech ID:' => 'tech.handle',
                'Tech Name:' => 'tech.name',
                'Tech Organization:' => 'tech.organization',
                'Tech Address:' => 'tech.address.street.',
                'Tech Address2:' => 'tech.address.street.',
                'Tech Address3:' => 'tech.address.street.',
                'Tech Street:' => 'tech.address.street.',
                'Tech Street1:' => 'tech.address.street.',
                'Tech Street2:' => 'tech.address.street.',
                'Tech Street3:' => 'tech.address.street.',
                'Tech City:' => 'tech.address.city',
                'Tech Postal Code:' => 'tech.address.pcode',
                'Tech State/Province:' => 'tech.address.state',
                'Tech Country:' => 'tech.address.country',
                'Tech Country/Economy:' => 'tech.address.country',
                'Tech Phone:' => 'tech.phone',
                'Tech FAX:' => 'tech.fax',
                'Tech Email:' => 'tech.email',
                'Tech E-mail:' => 'tech.email',
                'Tech Contact Name:' => 'tech.name',
                'Tech Contact ID:' => 'tech.handle',
                'Tech Contact Organisation:' => 'tech.organization',
                'Billing Contact ID:' => 'billing.handle',
                'Billing Contact Name:' => 'billing.name',
                'Billing Contact Organization:' => 'billing.organization',
                'Billing Contact Address1:' => 'billing.address.street.',
                'Billing Contact Address2:' => 'billing.address.street.',
                'Billing Contact Postal Code:' => 'billing.address.pcode',
                'Billing Contact City:' => 'billing.address.city',
                'Billing Contact State/Province:' => 'billing.address.state',
                'Billing Contact Country:' => 'billing.address.country',
                'Billing Contact Phone Number:' => 'billing.phone',
                'Billing Contact Facsimile Number:' => 'billing.fax',
                'Billing Contact Email:' => 'billing.email',
                'Billing ID:' => 'billing.handle',
                'Billing Name:' => 'billing.name',
                'Billing Organization:' => 'billing.organization',
                'Billing Address:' => 'billing.address.street.',
                'Billing Address1:' => 'billing.address.street.',
                'Billing Address2:' => 'billing.address.street.',
                'Billing Address3:' => 'billing.address.street.',
                'Billing Street:' => 'billing.address.street.',
                'Billing Street1:' => 'billing.address.street.',
                'Billing Street2:' => 'billing.address.street.',
                'Billing Street3:' => 'billing.address.street.',
                'Billing City:' => 'billing.address.city',
                'Billing Postal Code:' => 'billing.address.pcode',
                'Billing State/Province:' => 'billing.address.state',
                'Billing Country:' => 'billing.address.country',
                'Billing Country/Economy:' => 'billing.address.country',
                'Billing Country Code:' => 'billing.address.country',
                'Billing Phone:' => 'billing.phone',
                'Billing Fax:' => 'billing.fax',
                'Billing FAX:' => 'billing.fax',
                'Billing Email:' => 'billing.email',
                'Billing E-mail:' => 'billing.email',
                'Zone ID:' => 'zone.handle',
                'Zone Organization:' => 'zone.organization',
                'Zone Name:' => 'zone.name',
                'Zone Address:' => 'zone.address.street.',
                'Zone Address 2:' => 'zone.address.street.',
                'Zone City:' => 'zone.address.city',
                'Zone State/Province:' => 'zone.address.state',
                'Zone Postal Code:' => 'zone.address.pcode',
                'Zone Country:' => 'zone.address.country',
                'Zone Phone Number:' => 'zone.phone',
                'Zone Fax Number:' => 'zone.fax',
                'Zone Email:' => 'zone.email',
            ];
        }

        $r = [];
        $disok = true;

        foreach ($rawData as $val) {
            if ('' !== \trim($val)) {
                if (('%' === $val[0] || '#' === $val[0]) && $disok) {
                    $r['disclaimer'][] = \trim(\substr($val, 1));
                    $disok = true;
                    continue;
                }

                $disok = false;
                \reset($items);

                foreach ($items as $match => $field) {
                    $pos = \strpos($val, $match);

                    if (false !== $pos) {
                        if ('' !== $field) {
                            $itm = \trim(\substr($val, $pos + \strlen($match)));

                            if ('' !== $itm) {
                                $r = self::assign($r, $field, \str_replace('"', '\"', $itm));
                            }
                        }

                        if (!$scanAll) {
                            break;
                        }
                    }
                }
            }
        }

        if (!$r) {
            if ($hasReg) {
                $r['registered'] = 'no';
            }
        } else {
            if ($hasReg) {
                $r['registered'] = 'yes';
            }

            $r = $this->formatDates($r, $dateFormat);
        }

        return $r;
    }

    protected function getBlocks(array $rawData, array $items, bool $partialMatch = false, bool $defBlock = false): array
    {
        $r = [];
        $endtag = '';

        while ($val = \current($rawData)) {
            if (false === \next($rawData)) {
                // No more data
                break;
            }

            $val = \trim($val);
            if ('' === $val) {
                continue;
            }

            $var = $found = false;

            foreach ($items as $field => $match) {
                $pos = \strpos($val, $match);

                if ('' !== $field && false !== $pos) {
                    if ($val === $match) {
                        $found = true;
                        $endtag = '';
                        $line = $val;
                        break;
                    }

                    $last = $val[\strlen($val) - 1];

                    if (':' === $last || '-' === $last || ']' === $last) {
                        $found = true;
                        $endtag = $last;
                        $line = $val;
                    } else {
                        $var = \strtok($field, '#');
                        $r = self::assign($r, $var, \trim(\substr($val, $pos + \strlen($match))));
                    }

                    break;
                }
            }

            if (!$found) {
                if (!$var && $defBlock) {
                    $r[$defBlock][] = $val;
                }
                continue;
            }

            $block = [];

            // Block found, get data ...
            while ($val = \current($rawData)) {
                if (false === \next($rawData)) {
                    // No more data
                    break;
                }

                $val = \trim($val);

                if ('' === $val || $val === \str_repeat($val[0], \strlen($val))) {
                    continue;
                }

                $last = $val[\strlen($val) - 1];

                if ('' === $endtag || $partialMatch || $last === $endtag) {
                    // Check if this line starts another block
                    $et = false;

                    foreach ($items as $field => $match) {
                        $pos = \strpos($val, $match);

                        if (false !== $pos && 0 === $pos) {
                            $et = true;
                            break;
                        }
                    }

                    if ($et) {
                        // Another block found
                        \prev($rawData);
                        break;
                    }
                }

                $block[] = $val;
            }

            if (empty($block)) {
                continue;
            }

            foreach ($items as $field => $match) {
                $pos = \strpos($line, $match);

                if (false !== $pos) {
                    $var = \strtok($field, '#');
                    if ('[]' !== $var) {
                        $r = self::assign($r, $var, $block);
                    }
                }
            }
        }

        return $r;
    }

    protected function getContacts(array $array, array $extraItems = [], bool $hasOrg = false): array
    {
        if (isset($array['billing'])) {
            $array['billing'] = $this->getContact($array['billing'], $extraItems, $hasOrg);
        }

        if (isset($array['tech'])) {
            $array['tech'] = $this->getContact($array['tech'], $extraItems, $hasOrg);
        }

        if (isset($array['zone'])) {
            $array['zone'] = $this->getContact($array['zone'], $extraItems, $hasOrg);
        }

        if (isset($array['admin'])) {
            $array['admin'] = $this->getContact($array['admin'], $extraItems, $hasOrg);
        }

        if (isset($array['owner'])) {
            $array['owner'] = $this->getContact($array['owner'], $extraItems, $hasOrg);
        }

        if (isset($array['registrar'])) {
            $array['registrar'] = $this->getContact($array['registrar'], $extraItems, $hasOrg);
        }

        return $array;
    }

    protected function getContact($array, array $extraItems = [], bool $hasOrg = false): array
    {
        if (!\is_array($array)) {
            return [];
        }

        $items = [
            'fax..:' => 'fax',
            'fax.' => 'fax',
            'fax-no:' => 'fax',
            'fax -' => 'fax',
            'fax-' => 'fax',
            'fax::' => 'fax',
            'fax:' => 'fax',
            '[fax]' => 'fax',
            '(fax)' => 'fax',
            'fax' => 'fax',
            'tel. ' => 'phone',
            'tel:' => 'phone',
            'phone::' => 'phone',
            'phone:' => 'phone',
            'phone-' => 'phone',
            'phone -' => 'phone',
            'email:' => 'email',
            'e-mail:' => 'email',
            'company name:' => 'organization',
            'organisation:' => 'organization',
            'first name:' => 'name.first',
            'last name:' => 'name.last',
            'street:' => 'address.street',
            'address:' => 'address.street.',
            'language:' => '',
            'location:' => 'address.city',
            'country:' => 'address.country',
            'name:' => 'name',
            'last modified:' => 'changed',
        ];

        if ($extraItems) {
            foreach ($items as $match => $field) {
                if (!isset($extraItems[$match])) {
                    $extraItems[$match] = $field;
                }
            }
            $items = $extraItems;
        }

        $r = [];
        foreach ($array as $key => $val) {
            $ok = true;

            while ($ok) {
                \reset($items);
                $ok = false;

                foreach ($items as $match => $field) {
                    $pos = \stripos($val, $match);

                    if (false === $pos) {
                        continue;
                    }

                    $itm = \trim(\substr($val, $pos + \strlen($match)));

                    if ('' !== $field && '' !== $itm) {
                        $r = self::assign($r, $field, $itm);
                    }

                    $val = \trim(\substr($val, 0, $pos));

                    if ('' === $val) {
                        unset($array[$key]);
                        break;
                    }

                    $array[$key] = $val;
                    $ok = true;
                }

                if (\preg_match('/([+]*[-(). x0-9]){7,}/', $val, $matches)) {
                    $phone = \trim(\str_replace(' ', '', $matches[0]));

                    if (\strlen($phone) > 8 && !\preg_match('/\d{5}-\d{3}/', $phone)) {
                        if (isset($r['phone'])) {
                            if (isset($r['fax'])) {
                                continue;
                            }
                            $r['fax'] = \trim($matches[0]);
                        } else {
                            $r['phone'] = \trim($matches[0]);
                        }

                        $val = \str_replace($matches[0], '', $val);

                        if ('' === $val) {
                            unset($array[$key]);
                            continue;
                        }

                        $array[$key] = $val;
                        $ok = true;
                    }
                }

                if (\preg_match('/([-0-9a-zA-Z._+&\/=]+@([-0-9a-zA-Z]+[.])+[a-zA-Z]{2,6})/', $val, $matches)) {
                    $r['email'] = $matches[0];

                    $val = \str_replace($matches[0], '', $val);
                    $val = \trim(\str_replace('()', '', $val));

                    if ('' === $val) {
                        unset($array[$key]);
                        continue;
                    }

                    if (!isset($r['name'])) {
                        $r['name'] = $val;
                        unset($array[$key]);
                    } else {
                        $array[$key] = $val;
                    }

                    $ok = true;
                }
            }
        }

        if (!isset($r['name']) && \count($array) > 0) {
            $r['name'] = \array_shift($array);
        }

        if ($hasOrg && \count($array) > 0) {
            $r['organization'] = \array_shift($array);
        }

        if (isset($r['name']) && \is_array($r['name'])) {
            $r['name'] = \implode(' ', $r['name']);
        }

        if (!empty($array)) {
            if (isset($r['address'])) {
                $r['address'] = \array_merge($r['address'], $array);
            } else {
                $r['address'] = $array;
            }
        }

        return $r;
    }

    protected function parseRegistryInfo(array $rawData): array
    {
        $registryItems = [
            'Registrar URL:' => 'referrer',
            'Registrar Name:' => 'registrar',
            'Registrar:' => 'registrar',
            'Registrar Abuse Contact Email:' => 'abuse.email',
            'Registrar Abuse Contact Phone:' => 'abuse.phone',
            'Registrar WHOIS Server:' => 'whois',
        ];

        $registryInfo = $this->generic_parser_b($rawData, $registryItems);
        unset($registryInfo['registered']);

        return $registryInfo;
    }

    protected function getDate(string $date, string $format): string
    {
        $parsedDate = $this->parseStandardDate($date);
        if ($parsedDate) {
            return $parsedDate->format('Y-m-d');
        }

        $months = [
            'jan' => 1,
            'ene' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'abr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'ago' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
            'dic' => 12,
        ];

        $parts = \explode(' ', $date);

        if (\str_contains($parts[0], '@')) {
            unset($parts[0]);
            $date = \implode(' ', $parts);
        }

        $date = \str_replace([',', '.', '-', '/', "\t"], ' ', \trim($date));

        $parts = \explode(' ', $date);
        $res = [];

        if ((8 === \strlen($parts[0]) || 1 === \count($parts)) && \is_numeric($parts[0])) {
            $val = $parts[0];
            for ($p = $i = 0; $i < 3; ++$i) {
                if ('Y' !== $format[$i]) {
                    $res[$format[$i]] = \substr($val, $p, 2);
                    $p += 2;
                } else {
                    $res['y'] = \substr($val, $p, 4);
                    $p += 4;
                }
            }
        } else {
            $format = \strtolower($format);

            for ($p = $i = 0; $p < \count($parts) && $i < \strlen($format); ++$p) {
                if ('' === \trim($parts[$p])) {
                    continue;
                }

                if ('-' !== $format[$i]) {
                    $res[$format[$i]] = $parts[$p];
                }
                ++$i;
            }
        }

        if (!$res) {
            return $date;
        }

        $ok = false;

        while (!$ok) {
            $ok = true;

            foreach ($res as $key => $val) {
                if ('' === $val || '' === $key) {
                    continue;
                }

                if (!\is_numeric($val) && isset($months[\strtolower(\substr($val, 0, 3))])) {
                    $res[$key] = $res['m'];
                    $res['m'] = $months[\strtolower(\substr($val, 0, 3))];
                    $ok = false;
                    break;
                }

                if ('y' !== $key && 'Y' !== $key && $val > 1900) {
                    $res[$key] = $res['y'];
                    $res['y'] = $val;
                    $ok = false;
                    break;
                }
            }
        }

        if ($res['m'] > 12) {
            $v = $res['m'];
            $res['m'] = $res['d'];
            $res['d'] = $v;
        }

        if ($res['y'] < 70) {
            $res['y'] += 2000;
        } elseif ($res['y'] <= 99) {
            $res['y'] += 1900;
        }

        return \sprintf('%.4d-%02d-%02d', $res['y'], $res['m'], $res['d']);
    }

    protected function parseStandardDate(string $date): ?\DateTime
    {
        $date = \trim($date);
        $UTC = new \DateTimeZone('UTC');

        // Must be an array with: "pattern" => "PHP DateTime Format"
        $rules = [
            // 2020-01-01T00:00:00.0Z
            '/^(?<datetime>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})\.(?<microseconds>\d+)(?<timezone>Z)$/' => 'Y-m-d\TH:i:s.uT',

            // 2020-01-01T00:00:00Z
            '/^(?<datetime>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})(?<timezone>Z)$/' => 'Y-m-d\TH:i:sT',

            // 2020-01-01T00:00:00
            '/^(?<datetime>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2})$/' => 'Y-m-d\TH:i:s',

            // 2021-03-03T00:00:00-0800
            '/^(?<datetime>\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+]\d{4})$/' => 'Y-m-d\TH:i:sP',

            // 27-Jul-2016
            '/^(?<datetime>\d{2}-[a-zA-Z]{3}-\d{4})$/' => 'd-M-Y',

            // 2020-01-01 00:00:00 CLST
            '/^(?<datetime>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) ?(?<timezone>\w+)?$/' => 'Y-m-d H:i:s T',

            // 11-May-2016 05:18:45 UTC
            '/^(?<datetime>\d{2}-[A-Za-z]{3}-\d{4} \d{2}:\d{2}:\d{2}) (?<timezone>\w+)$/' => 'd-M-Y H:i:s T',

            // "domain-registrar AT isoc.org.il 20210913" => " 20210913"
            '/ ?(?<datetime>\d{8})( \(?[A-Za-z#\d]+\)?)?$/' => 'Ymd',

            // 20121116 16:58:21
            '/(?<datetime>\d{8} \d{2}:\d{2}:\d{2})$/' => 'Ymd H:i:s',

            // 2001/06/25 22:37:59
            '/(?<datetime>\d{4}\/\d{2}\/\d{2} \d{2}:\d{2}:\d{2})$/' => 'Y/m/d H:i:s',

            // 2019-03-31
            '/(?<datetime>\d{4}-\d{2}-\d{2})$/' => 'Y-m-d',

            // 1998/02/05
            '/(?<datetime>\d{4}\/\d{2}\/\d{2})$/' => 'Y/m/d',

            // 22.07.2023
            '/(?<datetime>\d{2}\.\d{2}\.\d{4})$/' => 'd.m.Y',

            // 31/05/1995
            // 23/08/2005 hostmaster@nic.fr
            '/(?<datetime>\d{2}\/\d{2}\/\d{4})( \w+@\w+\.\w+)?$/' => 'd/m/Y',

            // 9.12.2001 09:25:00
            // 30.6.2006 00:00:00
            '/(?<datetime>\d{1,2}\.\d{1,2}\.\d{4} \d{2}:\d{2}:\d{2})$/' => 'j.n.Y H:i:s',

            // 02.03.2018 18:52:05
            '/(?<datetime>\d{2}\.\d{2}\.\d{4} \d{2}:\d{2}:\d{2})$/' => 'd.m.Y H:i:s',

            // Wed Apr 1 1998
            '/[A-Za-z]{3} (?<datetime>[A-Za-z]{3} \d{1,2} \d{4})$/' => 'M d Y',

            // 1996.06.27 13:36:21
            '/(?<datetime>\d{4}\.\d{2}\.\d{2} \d{2}:\d{2}:\d{2})$/' => 'Y.m.d H:i:s',

            // 01-January-2025
            '/^(?<datetime>\d{2}-[A-Z][a-z]+-\d{4})$/' => 'd-F-Y',

            // November  6 2000
            '/^(?<datetime>[A-Z][a-z]+\s+\d{1,2}\s+\d{4})$/' => 'F j Y',
        ];

        foreach ($rules as $regex => $dateTimeFormat) {
            $matches = [];

            \preg_match($regex, $date, $matches);

            if (\preg_match($regex, $date, $matches)) {
                if (!empty($matches['microseconds']) && \PHP_VERSION_ID <= 80200) {
                    // For PHP <= 8.2, skip milliseconds
                    $date = $matches['datetime'];
                    continue;
                }

                $parsedDate = \DateTime::createFromFormat($dateTimeFormat, $date, $UTC);
                if ($parsedDate) {
                    return $parsedDate;
                }

                $parsedDate = \DateTime::createFromFormat($dateTimeFormat, $matches['datetime'] ?? $matches[0], $UTC);
                if ($parsedDate) {
                    return $parsedDate;
                }

                if (!empty($matches[1])) {
                    // Fallback, try ignoring the TimeZone
                    $parsedDate = \DateTime::createFromFormat('Y-m-d H:i:s', $matches[1], $UTC);
                    if ($parsedDate) {
                        return $parsedDate;
                    }
                }
            }
        }

        return null;
    }

    /**
     * @param array    $array The array to populate
     * @param string[] $parts
     * @param mixed    $value The value to be assigned to the $vDef key
     *
     * @return array The updated array
     *
     * @see https://github.com/sparc/phpWhois.org/compare/18849d1a98b992190612cdb2561e7b4492c505f5...8c6a18686775b25f05592dd67d7706e47167a498#diff-b8adbe1292f8abca1f943aa844db52aa Original fix by David Saez PAdros sparc
     */
    private static function assignRecursive(array $array, array $parts, $value): array
    {
        $key = \array_shift($parts);

        if (0 === \count($parts)) {
            if (!$key) {
                $array[] = $value;
            } else {
                $array[$key] = $value;
            }
        } else {
            if (!isset($array[$key])) {
                $array[$key] = [];
            }
            $array[$key] = self::assignRecursive($array[$key], $parts, $value);
        }

        return $array;
    }

    /**
     * @param array  $array The array to populate
     * @param string $vDef  A period-separated string of nested array keys
     * @param mixed  $value The value to be assigned to the $vDef key
     *
     * @return array The updated array
     *
     * @see https://github.com/sparc/phpWhois.org/compare/18849d1a98b992190612cdb2561e7b4492c505f5...8c6a18686775b25f05592dd67d7706e47167a498#diff-b8adbe1292f8abca1f943aa844db52aa Original fix by David Saez PAdros sparc
     */
    private static function assign(array $array, string $vDef, $value): array
    {
        return self::assignRecursive($array, \explode('.', $vDef), $value);
    }

    protected static function mergeRegrinfo(array $a1, array $a2): array
    {
        \reset($a2);

        foreach ($a2 as $key => $val) {
            if (isset($a1[$key])) {
                if (\is_array($val)) {
                    if ('nserver' !== $key) {
                        $a1[$key] = self::mergeRegrinfo($a1[$key], $val);
                    }
                } else {
                    $val = \trim($val);
                    if ('' !== $val) {
                        $a1[$key] = $val;
                    }
                }
            } else {
                $a1[$key] = $val;
            }
        }

        return $a1;
    }
}
