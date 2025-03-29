<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class GtldHandler extends AbstractHandler
{
    protected array $result = [];

    public const REG_FIELDS = [
        'Domain Name:' => 'regrinfo.domain.name',
        'Registrar:' => 'regyinfo.registrar',
        'Whois Server:' => 'regyinfo.whois',
        'Referral URL:' => 'regyinfo.referrer',
        'Name Server:' => 'regrinfo.domain.nserver.', // identical descriptors
        'Updated Date:' => 'regrinfo.domain.changed',
        'Last Updated On:' => 'regrinfo.domain.changed',
        'EPP Status:' => 'regrinfo.domain.epp_status.',
        'Status:' => 'regrinfo.domain.status.',
        'Creation Date:' => 'regrinfo.domain.created',
        'Created On:' => 'regrinfo.domain.created',
        'Expiration Date:' => 'regrinfo.domain.expires',
        'Registry Expiry Date:' => 'regrinfo.domain.expires',
        'No match for ' => 'nodomain',
    ];

    public function parse(array $data_str, string $query): array
    {
        $this->whoisClient->query->clear();
        $this->result = static::generic_parser_b($data_str['rawdata'], self::REG_FIELDS, 'dmy');

        unset($this->result['registered']);

        if (isset($this->result['nodomain'])) {
            unset($this->result['nodomain']);
            $this->result['regrinfo']['registered'] = 'no';

            return $this->result;
        }

        if ($this->deepWhois) {
            $this->result = $this->whoisClient->deepWhois($query, $this->result);
        }

        // Next server could fail to return data
        if (empty($this->result['rawdata']) || \count($this->result['rawdata']) < 3) {
            $this->result['rawdata'] = $data_str['rawdata'];
        }

        // Domain is registered no matter what next server says
        $this->result['regrinfo']['registered'] = 'yes';

        return $this->result;
    }
}
