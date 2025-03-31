<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class NzHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain_name:' => 'domain.name',
            'query_status:' => 'domain.status',
            'ns_name_01:' => 'domain.nserver.0',
            'ns_name_02:' => 'domain.nserver.1',
            'ns_name_03:' => 'domain.nserver.2',
            'domain_dateregistered:' => 'domain.created',
            'domain_datelastmodified:' => 'domain.changed',
            'domain_datebilleduntil:' => 'domain.expires',
            'registrar_name:' => 'domain.sponsor',
            'registrant_contact_name:' => 'owner.name',
            'registrant_contact_address1:' => 'owner.address.address.0',
            'registrant_contact_address2:' => 'owner.address.address.1',
            'registrant_contact_address3:' => 'owner.address.address.2',
            'registrant_contact_postalcode:' => 'owner.address.pcode',
            'registrant_contact_city:' => 'owner.address.city',
            'Registrant State/Province:' => 'owner.address.state',
            'registrant_contact_country:' => 'owner.address.country',
            'registrant_contact_phone:' => 'owner.phone',
            'registrant_contact_fax:' => 'owner.fax',
            'registrant_contact_email:' => 'owner.email',
            'admin_contact_name:' => 'admin.name',
            'admin_contact_address1:' => 'admin.address.address.0',
            'admin_contact_address2:' => 'admin.address.address.1',
            'admin_contact_address3:' => 'admin.address.address.2',
            'admin_contact_postalcode:' => 'admin.address.pcode',
            'admin_contact_city:' => 'admin.address.city',
            'admin_contact_country:' => 'admin.address.country',
            'admin_contact_phone:' => 'admin.phone',
            'admin_contact_fax:' => 'admin.fax',
            'admin_contact_email:' => 'admin.email',
            'technical_contact_name:' => 'tech.name',
            'technical_contact_address0:' => 'tech.address.address.0',
            'technical_contact_address1:' => 'tech.address.address.1',
            'technical_contact_address2:' => 'tech.address.address.2',
            'technical_contact_postalcode:' => 'tech.address.pcode',
            'technical_contact_city:' => 'tech.address.city',
            'technical_contact_country:' => 'tech.address.country',
            'technical_contact_phone:' => 'tech.phone',
            'technical_contact_fax:' => 'tech.fax',
            'technical_contact_email:' => 'tech.email',
        ];

        $data = new Data();
        $data->regrinfo = $this->generic_parser_b($rawData, $items);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.dnc.org.nz',
            'registrar' => 'New Zealand Domain Name Registry Limited',
        ] : [];
        $data->rawData = $rawData;

        $domainStatus = null;
        if (!empty($data->regrinfo['domain']['status'])) {
            $domainStatus = \substr($data->regrinfo['domain']['status'], 0, 3);
        }

        if ('200' === $domainStatus) {
            $data->regrinfo['registered'] = 'yes';
        } elseif ('220' === $domainStatus) {
            $data->regrinfo['registered'] = 'no';
        } else {
            $data->regrinfo['registered'] = 'unknown';
        }

        return $data;
    }

    protected function generic_parser_a_blocks(array $rawData, array $translate, array &$disclaimer = []): array
    {
        $blocks = parent::generic_parser_a_blocks($rawData, $translate, $disclaimer);

        \array_walk_recursive($blocks, static function (string &$v, string $key): void {
            if (!\in_array($key, ['expires', 'created', 'changed'], true)) {
                return;
            }

            $matches = [];
            $pattern = '/(\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[-+])(\d{2}):(\d{2})/';
            \preg_match($pattern, $v, $matches);

            if (!empty($matches)) {
                $v = $matches[1].$matches[2].$matches[3];
            }
        });

        return $blocks;
    }
}
