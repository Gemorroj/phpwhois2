<?php

namespace PHPWhois2\Handlers;

/**
 * @TODO BUG
 * - date on ro could be given as "mail date" (ex: updated field)
 * - multiple person for one role, ex: news.ro
 * - seems the only role listed is registrant
 */
class RoHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl' => 'handle',
            'person' => 'name',
            'address' => 'address.',
            'domain-name' => '',
            'updated' => 'changed',
            'registration-date' => 'created',
            'domain-status' => 'status',
            'nameserver' => 'nserver',
        ];

        $contacts = [
            'admin-contact' => 'admin',
            'technical-contact' => 'tech',
            'zone-contact' => 'zone',
            'billing-contact' => 'billing',
        ];

        $extra = [
            'postal code:' => 'address.pcode',
        ];

        $reg = static::generic_parser_a($data_str['rawdata'], $translate, $contacts, 'domain', 'Ymd');

        if (isset($reg['domain']['description'])) {
            $reg['owner'] = static::getContact($reg['domain']['description'], $extra);
            unset($reg['domain']['description']);

            foreach ($reg as $key => $item) {
                if (isset($item['address'])) {
                    $data = $item['address'];
                    unset($reg[$key]['address']);
                    $reg[$key] = \array_merge($reg[$key], static::getContact($data, $extra));
                }
            }

            $reg['registered'] = 'yes';
        } else {
            $reg['registered'] = 'no';
        }

        return [
            'regrinfo' => $reg,
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.nic.ro',
                'registrar' => 'nic.ro',
            ],
            'rawdata' => $data_str['rawdata'],
        ];
    }
}
