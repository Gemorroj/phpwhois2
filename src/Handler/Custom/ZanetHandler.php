<?php

namespace PHPWhois2\Handler\Custom;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class ZanetHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $items = [
            'domain.name' => 'Domain Name            : ',
            'domain.created' => 'Record Created         :',
            'domain.changed' => 'Record	Last Updated    :',
            'owner.name' => 'Registered for         :',
            'admin' => 'Administrative Contact :',
            'tech' => 'Technical Contact      :',
            'domain.nserver' => 'Domain Name Servers listed in order:',
            'registered' => 'No such domain: ',
            '' => 'The ZA NiC whois',
        ];

        // Arrange contacts ...
        $formattedRawData = [];
        foreach ($rawData as $line) {
            if (\str_contains($line, ' Contact ')) {
                $pos = \strpos($line, ':');

                if (false !== $pos) {
                    $formattedRawData[] = \substr($line, 0, $pos + 1);
                    $formattedRawData[] = \trim(\substr($line, $pos + 1));
                    continue;
                }
            }
            $formattedRawData[] = $line;
        }

        $data = new Data();
        $data->regrinfo = $this->getBlocks($formattedRawData, $items);
        $data->regyinfo = $this->parseRegistryInfo($rawData) ? [
            // Or try http://www.za.org
            'referrer' => 'https://www.za.net/',
            'registrar' => 'ZA NiC',
        ] : [];
        $data->rawData = $rawData;

        if (isset($data->regrinfo['registered'])) {
            $data->regrinfo['registered'] = 'no';
        } else {
            if (isset($data->regrinfo['admin'])) {
                $data->regrinfo['admin'] = $this->getContact($data->regrinfo['admin']);
            }

            if (isset($data->regrinfo['tech'])) {
                $data->regrinfo['tech'] = $this->getContact($data->regrinfo['tech']);
            }
        }

        $this->formatDates($data->regrinfo, 'xmdxxy');

        return $data;
    }
}
