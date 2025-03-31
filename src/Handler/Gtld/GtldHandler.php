<?php

namespace PHPWhois2\Handler\Gtld;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class GtldHandler extends AbstractHandler
{
    private const REG_FIELDS = [
        'Domain Name:' => 'regrinfo.domain.name',
        'Registrar:' => 'regyinfo.registrar',
        'Registrar WHOIS Server:' => 'regyinfo.whois',
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

    public function parse(array $rawData, string $query): Data
    {
        // $this->whoisClient->queryParams->clear();
        $this->whoisClient->queryParams->args = null;
        $this->whoisClient->queryParams->handler = null;
        $this->whoisClient->queryParams->handlerClass = self::class;
        $result = $this->generic_parser_b($rawData, self::REG_FIELDS);

        $data = new Data();
        $data->regrinfo = $result['regrinfo'];
        $data->regyinfo = $result['regyinfo'];
        $data->rawData = $rawData;

        if (isset($result['nodomain'])) {
            $data->regrinfo['registered'] = 'no';

            return $data;
        }

        if (isset($data->regyinfo['whois'])) {
            $this->whoisClient->queryParams->server = $data->regyinfo['whois'];
            $subRawData = $this->whoisClient->getRawData($query);
            if ($subRawData) {
                \array_push($data->rawData, "\n\n", '-----------------------------------', "\n\n", ...$subRawData);
                $this->whoisClient->makeWhoisInfo($data);

                $subRegrinfo = $this->generic_parser_b($subRawData);
                $data->regrinfo = self::mergeRegrinfo($data->regrinfo, $subRegrinfo);
            }
        }

        // Domain is registered no matter what next server says
        $data->regrinfo['registered'] = 'yes';

        return $data;
    }
}
