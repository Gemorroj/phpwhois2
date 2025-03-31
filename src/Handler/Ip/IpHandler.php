<?php

namespace PHPWhois2\Handler\Ip;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;
use PHPWhois2\WhoisClient;

class IpHandler extends AbstractHandler
{
    private const REGISTRARS = [
        'European Regional Internet Registry/RIPE NCC' => 'whois.ripe.net',
        'RIPE Network Coordination Centre' => 'whois.ripe.net',
        'Asia Pacific Network Information	Center' => 'whois.apnic.net',
        'Asia Pacific Network Information Centre' => 'whois.apnic.net',
        'Latin American and Caribbean IP address Regional Registry' => 'whois.lacnic.net',
        'African Network Information Center' => 'whois.afrinic.net',
        'Korean Network Information Centre' => 'whois.krnic.net',
    ];

    private const HANDLERS = [
        'whois.krnic.net' => 'krnic',
        'whois.apnic.net' => 'apnic',
        'whois.ripe.net' => 'ripe',
        'whois.arin.net' => 'arin',
        'whois.lacnic.net' => 'lacnic',
        'whois.afrinic.net' => 'afrinic',
    ];

    public function parse(array $rawData, string $query): Data
    {
        $result = new Data();
        $result->regyinfo['registrar'] = 'American Registry for Internet Numbers (ARIN)';

        if (!\str_contains($query, '.')) {
            $result->regyinfo['type'] = 'AS';
        } else {
            $result->regyinfo['type'] = 'ip';
        }

        // $this->whoisClient->queryParams->clear();
        $this->whoisClient->queryParams->args = null;
        // $this->whoisClient->queryParams->server = 'whois.arin.net';
        $this->whoisClient->queryParams->query = $query;
        $this->whoisClient->queryParams->handlerClass = self::class;
        $this->whoisClient->queryParams->handler = 'arin';

        if (!$rawData) {
            return $result;
        }

        $registerOrgServer = null;

        $moreData = [];
        foreach ($rawData as $line) {
            if (\str_contains($line, 'American Registry for Internet Numbers')) {
                continue;
            }

            $registerOrgServer = $this->registrasContainsServer($line);
            if ($registerOrgServer) {
                $moreData = [
                    'query' => $query,
                    'server' => $registerOrgServer,
                    'handler' => self::HANDLERS[$registerOrgServer],
                    'reset' => true,
                ];
                break;
            }
        }

        if (!$registerOrgServer) {
            $this->parseResults($result, $rawData, true);
        }

        if ($moreData) {
            $this->whoisClient->queryParams->server = $moreData['server'];
            $this->whoisClient->queryParams->handler = $moreData['handler'];

            // Use original query
            $rwdata = $this->whoisClient->getRawData($moreData['query']);
            if ($rwdata) {
                $this->parseResults($result, $rwdata, $moreData['reset']);
                $this->whoisClient->makeWhoisInfo($result);
            }
        }

        // Normalize nameserver fields
        if (isset($result->regrinfo['network']['nserver'])) {
            if (!\is_array($result->regrinfo['network']['nserver'])) {
                unset($result->regrinfo['network']['nserver']);
            } else {
                $result->regrinfo['network']['nserver'] = WhoisClient::fixNameServer($result->regrinfo['network']['nserver']);
            }
        }

        return $result;
    }

    private function registrasContainsServer(string $line): ?string
    {
        foreach (self::REGISTRARS as $registerOrg => $registerOrgServer) {
            if (\str_contains($line, $registerOrg)) {
                return $registerOrgServer;
            }
        }

        return null;
    }

    private function parseResults(Data $result, array $rwdata, bool $reset): void
    {
        $rwres = $this->whoisClient->process($rwdata);

        if ('AS' === $result->regyinfo['type'] && !empty($rwres->regrinfo['network'])) {
            $rwres->regrinfo['AS'] = $rwres->regrinfo['network'];
            unset($rwres->regrinfo['network']);
        }

        if ($reset) {
            $result->regrinfo = $rwres->regrinfo;
            $result->rawData = $rwdata;
        } else {
            $result->rawData[] = '';

            foreach ($rwdata as $line) {
                $result->rawData[] = $line;
            }

            foreach ($rwres->regrinfo as $key => $_) {
                $this->joinResult($result, $key, $rwres);
            }
        }

        if ($rwres->regyinfo) {
            $result->regyinfo = \array_merge($result->regyinfo, $rwres->regyinfo);
        }
    }

    private function joinResult(Data $result, string $key, Data $newres): void
    {
        if (isset($result->regrinfo[$key]) && !\array_key_exists(0, $result->regrinfo[$key])) {
            $r = $result->regrinfo[$key];
            $result->regrinfo[$key] = [$r];
        }

        $result->regrinfo[$key][] = $newres->regrinfo[$key];
    }
}
