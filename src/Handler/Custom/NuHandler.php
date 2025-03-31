<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class NuHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'name' => 'Domain Name (UTF-8):',
            'created' => 'Record created on',
            'expires' => 'Record expires on',
            'changed' => 'Record last updated on',
            'status' => 'Record status:',
            'handle' => 'Record ID:',
        ];

        $data = new Data();
        $data->regrinfo = [];
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            'whois' => 'whois.nic.nu',
            'referrer' => 'http://www.nunames.nu',
            'registrar' => '.NU Domain, Ltd',
        ] : [];
        $data->rawData = $rawData;

        foreach ($rawData as $val) {
            $val = \trim($val);

            if ('' !== $val) {
                if ('Domain servers in listed order:' === $val) {
                    foreach ($rawData as $val2) {
                        $val2 = \trim($val2);
                        if ('' === $val2) {
                            break;
                        }
                        $data->regrinfo['domain']['nserver'][] = $val2;
                    }
                    break;
                }

                foreach ($items as $field => $match) {
                    if (\str_contains($val, $match)) {
                        $data->regrinfo['domain'][$field] = \trim(\substr($val, \strlen($match)));
                        break;
                    }
                }
            }
        }

        if (isset($data->regrinfo['domain'])) {
            $data->regrinfo['registered'] = 'yes';
        } else {
            $data->regrinfo['registered'] = 'no';
        }

        $this->formatDates($data->regrinfo, 'dmy');

        return $data;
    }
}
