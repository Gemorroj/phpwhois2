<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class FrHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl' => 'handle',
            'ns-list' => 'handle',
            'person' => 'name',
            'address' => 'address.',
            'descr' => 'desc',
            'anniversary' => '',
            'domain' => 'name',
            'last-update' => 'changed',
            'registered' => 'created',
            'Expiry Date' => 'expires',
            'country' => 'address.country',
            'registrar' => 'sponsor',
            'role' => 'organization',
        ];

        $contacts = [
            'admin-c' => 'admin',
            'tech-c' => 'tech',
            'zone-c' => 'zone',
            'holder-c' => 'owner',
            'nsl-id' => 'nserver',
        ];

        $reg = $this->generic_parser_a($rawData, $translate, $contacts, 'domain', 'dmY');

        if (isset($reg['nserver'])) {
            $reg['domain'] = \array_merge($reg['domain'], $reg['nserver']);
            unset($reg['nserver']);
        }

        $data = new Data();
        $data->regrinfo = $reg;
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'referrer' => 'https://www.nic.fr',
            'registrar' => 'AFNIC',
        ] : [];
        $data->rawData = $rawData;

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
            $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z/';
            if (\preg_match($pattern, $v, $matches)) {
                $v = $matches[0];
            }
        });

        return $blocks;
    }
}
