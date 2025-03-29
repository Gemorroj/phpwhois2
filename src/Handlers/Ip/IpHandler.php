<?php

/**
 * @license   See LICENSE file
 * @copyright Copyright (C)1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @copyright Maintained by David Saez
 * @copyright Copyright (c) 2014 Dmitry Lukashin
 * @copyright Copyright (c) 2023 Kevin Lucich
 */

namespace phpWhois\Handlers\Ip;

use phpWhois\Handlers\AbstractHandler;
use phpWhois\QueryParams;
use phpWhois\WhoisClient;

class IpHandler extends AbstractHandler
{
    public array $REGISTRARS = [
        'European Regional Internet Registry/RIPE NCC' => 'whois.ripe.net',
        'RIPE Network Coordination Centre' => 'whois.ripe.net',
        'Asia Pacific Network Information	Center' => 'whois.apnic.net',
        'Asia Pacific Network Information Centre' => 'whois.apnic.net',
        'Latin American and Caribbean IP address Regional Registry' => 'whois.lacnic.net',
        'African Network Information Center' => 'whois.afrinic.net',
    ];

    public array $HANDLERS = [
        'whois.krnic.net' => 'krnic',
        'whois.apnic.net' => 'apnic',
        'whois.ripe.net' => 'ripe',
        'whois.arin.net' => 'arin',
        'whois.lacnic.net' => 'lacnic',
        'whois.afrinic.net' => 'afrinic',
    ];

    public array $more_data = []; // More queries to get more accurated data
    public array $done = [];

    public function parse(array $data_str, string $query): array
    {
        $result = [
            'regrinfo' => [],
            'regyinfo' => [],
            'rawdata' => [],
        ];
        $result['regyinfo']['registrar'] = 'American Registry for Internet Numbers (ARIN)';

        if (!\str_contains($query, '.')) {
            $result['regyinfo']['type'] = 'AS';
        } else {
            $result['regyinfo']['type'] = 'ip';
        }

        if (!$this->deepWhois) {
            return [];
        }

        $this->whoisClient->query = new QueryParams();
        $this->whoisClient->query->server = 'whois.arin.net';
        $this->whoisClient->query->query = $query;

        $rawdata = $data_str['rawdata'];

        if (empty($rawdata)) {
            return $result;
        }

        $presults = [];
        $presults[] = $rawdata;
        $ip = \ip2long($query);
        $done = [];

        while (\count($presults) > 0) {
            $rwdata = \array_shift($presults);
            $found = false;

            foreach ($rwdata as $line) {
                if (!\strncmp($line, 'American Registry for Internet Numbers', 38)) {
                    continue;
                }

                $p = \strpos($line, '(NETBLK-');

                if (false === $p) {
                    $p = \strpos($line, '(NET-');
                }

                if (false !== $p) {
                    $net = \strtok(\substr($line, $p + 1), ') ');
                    $clearedLine = \str_replace(' ', '', \substr($line, $p + \strlen($net) + 3));
                    if ('' !== $clearedLine) {
                        [$low, $high] = \explode('-', \str_replace(' ', '', \substr($line, $p + \strlen($net) + 3)));

                        if (!isset($done[$net]) && $ip >= \ip2long($low) && $ip <= \ip2long($high)) {
                            if (!empty($this->REGISTRARS['owner'])) {
                                $this->handle_rwhois($this->REGISTRARS['owner'], $query);
                                break 2;
                            }

                            $this->whoisClient->query->args = 'n '.$net;
                            $presults[] = $this->whoisClient->getRawData($net);
                            $done[$net] = 1;
                        }
                        $found = true;
                    }
                }
            }

            if (!$found) {
                $this->whoisClient->query->handler = 'arin';
                $result = $this->parse_results($result, $rwdata, $query, true);
            }
        }

        $this->whoisClient->query->args = null;

        while (\count($this->more_data) > 0) {
            $srv_data = \array_shift($this->more_data);
            $this->whoisClient->query->server = $srv_data['server'];
            $this->whoisClient->query->handler = null;
            // Use original query
            $rwdata = $this->whoisClient->getRawData($srv_data['query']);

            if (!empty($rwdata)) {
                if (!empty($srv_data['handler'])) {
                    $this->whoisClient->query->handler = $srv_data['handler'];
                }

                $result = $this->parse_results($result, $rwdata, $query, $srv_data['reset']);
                $result = $this->whoisClient->makeWhoisInfo($result);
            }
        }

        // Normalize nameserver fields

        if (isset($result['regrinfo']['network']['nserver'])) {
            if (!\is_array($result['regrinfo']['network']['nserver'])) {
                unset($result['regrinfo']['network']['nserver']);
            } else {
                $result['regrinfo']['network']['nserver'] = WhoisClient::fixNameServer($result['regrinfo']['network']['nserver']);
            }
        }

        return $result;
    }

    // -----------------------------------------------------------------

    public function parse_results(array $result, array $rwdata, string $query, bool $reset): array
    {
        $rwres = $this->whoisClient->process($rwdata);

        if ('AS' === $result['regyinfo']['type'] && !empty($rwres['regrinfo']['network'])) {
            $rwres['regrinfo']['AS'] = $rwres['regrinfo']['network'];
            unset($rwres['regrinfo']['network']);
        }

        if ($reset) {
            $result['regrinfo'] = $rwres['regrinfo'];
            $result['rawdata'] = $rwdata;
        } else {
            $result['rawdata'][] = '';

            foreach ($rwdata as $line) {
                $result['rawdata'][] = $line;
            }

            foreach ($rwres['regrinfo'] as $key => $_) {
                $result = $this->join_result($result, $key, $rwres);
            }
        }

        if ($this->deepWhois) {
            if (isset($rwres['regrinfo']['rwhois'])) {
                $this->handle_rwhois($rwres['regrinfo']['rwhois'], $query);
                unset($result['regrinfo']['rwhois']);
            } elseif (!@empty($rwres['regrinfo']['owner']['organization'])) {
                switch ($rwres['regrinfo']['owner']['organization']) {
                    case 'KRNIC':
                        $this->handle_rwhois('whois.krnic.net', $query);
                        break;

                    case 'African Network Information Center':
                        $this->handle_rwhois('whois.afrinic.net', $query);
                        break;
                }
            }
        }

        if (!empty($rwres['regyinfo'])) {
            $result['regyinfo'] = \array_merge($result['regyinfo'], $rwres['regyinfo']);
        }

        return $result;
    }

    // -----------------------------------------------------------------

    public function handle_rwhois(string $server, string $query): void
    {
        // Avoid querying the same server twice

        $parts = \parse_url($server);

        if (empty($parts['host'])) {
            $host = $parts['path'];
        } else {
            $host = $parts['host'];
        }

        if (\array_key_exists($host, $this->done)) {
            return;
        }

        $q = [
            'query' => $query,
            'server' => $server,
        ];

        if (isset($this->HANDLERS[$host])) {
            $q['handler'] = $this->HANDLERS[$host];
            $q['file'] = \sprintf('whois.ip.%s.php', $q['handler']);
            $q['reset'] = true;
        } else {
            $q['handler'] = 'rwhois';
            $q['reset'] = false;
            unset($q['file']);
        }

        $this->more_data[] = $q;
        $this->done[$host] = 1;
    }

    // -----------------------------------------------------------------

    public function join_result(array $result, string $key, array $newres): array
    {
        if (isset($result['regrinfo'][$key]) && !\array_key_exists(0, $result['regrinfo'][$key])) {
            $r = $result['regrinfo'][$key];
            $result['regrinfo'][$key] = [$r];
        }

        $result['regrinfo'][$key][] = $newres['regrinfo'][$key];

        return $result;
    }
}
