<?php

namespace PHPWhois2\Handlers;

class JpHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            '[State]' => 'domain.status',
            '[Status]' => 'domain.status',
            '[Registered Date]' => 'domain.created',
            '[Created on]' => 'domain.created',
            '[Expires on]' => 'domain.expires',
            '[Last Updated]' => 'domain.changed',
            '[Last Update]' => 'domain.changed',
            '[Organization]' => 'owner.organization',
            '[Name]' => 'owner.name',
            '[Email]' => 'owner.email',
            '[Postal code]' => 'owner.address.pcode',
            '[Postal Address]' => 'owner.address.street',
            '[Phone]' => 'owner.phone',
            '[Fax]' => 'owner.fax',
            '[Administrative Contact]' => 'admin.handle',
            '[Technical Contact]' => 'tech.handle',
            '[Name Server]' => 'domain.nserver.',
        ];

        $r = [];
        $r['regrinfo'] = static::generic_parser_b($data_str['rawdata'], $items, 'ymd');

        $r['regyinfo'] = [
            'referrer' => 'http://www.jprs.jp',
            'registrar' => 'Japan Registry Services',
        ];

        if (!$this->deepWhois) {
            return $r;
        }

        $r['rawdata'] = $data_str['rawdata'];

        $items = [
            'a. [JPNIC Handle]' => 'handle',
            'c. [Last, First]' => 'name',
            'd. [E-Mail]' => 'email',
            'g. [Organization]' => 'organization',
            'o. [TEL]' => 'phone',
            'p. [FAX]' => 'fax',
            '[Last Update]' => 'changed',
        ];

        $this->whoisClient->query->server = 'jp.whois-servers.net';

        if (!empty($r['regrinfo']['admin']['handle'])) {
            $rwdata = $this->whoisClient->getRawData('CONTACT '.$r['regrinfo']['admin']['handle'].'/e');
            $r['rawdata'][] = '';
            $r['rawdata'] = \array_merge($r['rawdata'], $rwdata);
            $r['regrinfo']['admin'] = static::generic_parser_b($rwdata, $items, 'ymd', false);
            $r = $this->whoisClient->makeWhoisInfo($r);
        }

        if (!empty($r['regrinfo']['tech']['handle'])) {
            if (!empty($r['regrinfo']['admin']['handle']) && $r['regrinfo']['admin']['handle'] == $r['regrinfo']['tech']['handle']) {
                $r['regrinfo']['tech'] = $r['regrinfo']['admin'];
            } else {
                $this->whoisClient->query->clear();
                $this->whoisClient->query->server = 'jp.whois-servers.net';
                $rwdata = $this->whoisClient->getRawData('CONTACT '.$r['regrinfo']['tech']['handle'].'/e');
                $r['rawdata'][] = '';
                $r['rawdata'] = \array_merge($r['rawdata'], $rwdata);
                $r['regrinfo']['tech'] = static::generic_parser_b($rwdata, $items, 'ymd', false);
                $r = $this->whoisClient->makeWhoisInfo($r);
            }
        }

        return $r;
    }
}
