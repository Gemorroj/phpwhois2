<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

/**
 * @TODO BUG
 * - date on ro could be given as "mail date" (ex: updated field)
 * - multiple person for one role, ex: news.ro
 * - seems the only role listed is registrant
 */
class RoHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
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

        $reg = $this->generic_parser_a($rawData, $translate, $contacts, 'domain', 'Ymd');

        if (isset($reg['domain']['description'])) {
            $reg['owner'] = $this->getContact($reg['domain']['description'], $extra);
            unset($reg['domain']['description']);

            foreach ($reg as $key => $item) {
                if (isset($item['address'])) {
                    $data = $item['address'];
                    unset($reg[$key]['address']);
                    $reg[$key] = \array_merge($reg[$key], $this->getContact($data, $extra));
                }
            }

            $reg['registered'] = 'yes';
        } else {
            $reg['registered'] = 'no';
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.nic.ro',
            'registrar' => 'nic.ro',
        ] : [];
        $data->rawData = $rawData;

        return $data;
    }
}
