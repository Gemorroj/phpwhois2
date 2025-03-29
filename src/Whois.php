<?php

/**
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @license
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @see http://phpwhois.pw
 *
 * @copyright Copyright (C)1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @copyright Maintained by David Saez
 * @copyright Copyright (c) 2014 Dmitry Lukashin
 */

namespace phpWhois;

use Algo26\IdnaConvert\Exception\AlreadyPunycodeException;
use Algo26\IdnaConvert\Exception\InvalidCharacterException;
use Algo26\IdnaConvert\ToIdn;

class Whois
{
    /** @var bool Deep whois? */
    public bool $deepWhois = true;

    public const QTYPE_UNKNOWN = 0;
    public const QTYPE_DOMAIN = 1;
    public const QTYPE_IPV4 = 2;
    public const QTYPE_IPV6 = 3;
    public const QTYPE_AS = 4;
    private WhoisClient $whoisClient;

    public function __construct(?WhoisClient $whoisClient = null)
    {
        $this->whoisClient = $whoisClient ?? new WhoisClient();
    }

    /**
     * Use special whois server (Populate WHOIS_SPECIAL array).
     *
     * @param string $tld    Top-level domain
     * @param string $server Server address
     */
    public function useServer(string $tld, string $server): void
    {
        $this->whoisClient->WHOIS_SPECIAL[$tld] = $server;
    }

    /**
     *  Lookup query and return raw whois data.
     *
     * @param bool $isUtf True if domain name encoding is UTF-8 already, otherwise convert it to UTF-8
     */
    public function whois(string $domain, bool $isUtf = true): string
    {
        $lookup = $this->lookup($domain, $isUtf);

        return \implode(\PHP_EOL, $lookup['rawdata']);
    }

    /**
     *  Lookup query.
     *
     * @param string $query Domain name or other entity
     * @param bool   $isUtf True if domain name encoding is UTF-8 already, otherwise convert it to UTF-8
     *
     * @throws InvalidCharacterException
     */
    public function lookup(string $query = '', bool $isUtf = true): array
    {
        // start clean
        $this->whoisClient->query = new QueryParams();

        $query = \trim($query);
        if (!$isUtf) {
            $query = \mb_convert_encoding($query, 'UTF-8', 'ISO-8859-1');
        }
        try {
            $query = (new ToIdn())->convert($query);
        } catch (AlreadyPunycodeException $e) {
            // $query is already a Punycode
        }

        // If domain to query was not set
        if (!$query) {
            return ['rawdata' => []];
        }

        // Set domain to query in query array
        $this->whoisClient->query->query = $domain = $query = \strtolower($query);

        // Find a query type
        $qType = $this->getQueryType($query);

        switch ($qType) {
            case self::QTYPE_IPV4:
                // IPv4 Prepare to do lookup via the 'ip' handler
                $ip = @\gethostbyname($query);

                if (isset($this->WHOIS_SPECIAL['ip'])) {
                    $this->whoisClient->query->server = $this->WHOIS_SPECIAL['ip'];
                    $this->whoisClient->query->args = $ip;
                } else {
                    $this->whoisClient->query->server = 'whois.arin.net';
                    $this->whoisClient->query->args = "n $ip";
                    $this->whoisClient->query->handler = 'ip';
                }
                $this->whoisClient->query->hostIp = $ip;
                $this->whoisClient->query->query = $ip;
                $this->whoisClient->query->tld = 'ip';
                $hostName = @\gethostbyaddr($ip);
                if (false !== $hostName) {
                    $this->whoisClient->query->hostName = $hostName;
                }

                return $this->whoisClient->getData($this->deepWhois);

            case self::QTYPE_IPV6:
                // IPv6 AS Prepare to do lookup via the 'ip' handler
                $ip = @\gethostbyname($query);

                if (isset($this->WHOIS_SPECIAL['ip'])) {
                    $this->whoisClient->query->server = $this->WHOIS_SPECIAL['ip'];
                } else {
                    $this->whoisClient->query->server = 'whois.ripe.net';
                    $this->whoisClient->query->handler = 'ripe';
                }
                $this->whoisClient->query->query = $ip;
                $this->whoisClient->query->tld = 'ip';

                return $this->whoisClient->getData($this->deepWhois);

            case self::QTYPE_AS:
                // AS Prepare to do lookup via the 'ip' handler
                $ip = @\gethostbyname($query);
                $this->whoisClient->query->server = 'whois.arin.net';
                if (0 === \stripos($ip, 'as')) {
                    $as = \substr($ip, 2);
                } else {
                    $as = $ip;
                }
                $this->whoisClient->query->args = "a $as";
                $this->whoisClient->query->handler = 'ip';
                $this->whoisClient->query->query = $ip;
                $this->whoisClient->query->tld = 'as';

                return $this->whoisClient->getData($this->deepWhois);
        }

        // Build array of all possible tld's for that domain
        $tld = '';
        $server = '';
        $dp = \explode('.', $domain);
        $np = \count($dp) - 1;
        $tldtests = [];

        for ($i = 0; $i < $np; ++$i) {
            \array_shift($dp);
            $tldtests[] = \implode('.', $dp);
        }

        // Search the correct whois server
        $special_tlds = $this->whoisClient->WHOIS_SPECIAL;

        foreach ($tldtests as $tld) {
            // Test if we know in advance that no whois server is
            // available for this domain and that we can get the
            // data via http or whois request
            if (isset($special_tlds[$tld])) {
                $val = $special_tlds[$tld];

                if ('' == $val) {
                    return $this->unknown();
                }

                $domain = \substr($query, 0, -\strlen($tld) - 1);
                $val = \str_replace('{domain}', $domain, $val);
                $server = \str_replace('{tld}', $tld, $val);
                break;
            }
        }

        if ('' == $server) {
            foreach ($tldtests as $tld) {
                // Determine the top level domain, and it's whois server using
                // DNS lookups on 'whois-servers.net'.
                // Assumes a valid DNS response indicates a recognised tld (!?)
                $cname = $tld.'.whois-servers.net';

                if (\gethostbyname($cname) === $cname) {
                    continue;
                }
                $server = $tld.'.whois-servers.net';
                break;
            }
        }

        if ($tld && $server) {
            // If found, set tld and whois server in query array
            $this->whoisClient->query->server = $server;
            $this->whoisClient->query->tld = $tld;
            $handler = '';

            foreach ($tldtests as $htld) {
                // special handler exists for the tld ?
                if (isset($this->DATA[$htld])) {
                    $handler = $this->DATA[$htld];
                    break;
                }
            }

            // If there is a handler set it
            if ($handler) {
                $this->whoisClient->query->handler = $handler;
            }

            // Special parameters ?
            if (isset($this->WHOIS_PARAM[$server])) {
                $param = $this->WHOIS_PARAM[$server];
                $param = \str_replace('$domain', $domain, $param);
                $param = \str_replace('$tld', $tld, $param);
                $this->whoisClient->query->server .= '?'.$param;
            }

            $result = $this->whoisClient->getData($this->deepWhois);
            $this->checkDns($result);

            return $result;
        }

        // If tld not known, and domain not in DNS, return error
        return $this->unknown();
    }

    /**
     * Unsupported domains.
     */
    public function unknown(): array
    {
        $this->whoisClient->query->server = null;
        $this->whoisClient->query->status = 'error';
        $result = ['rawdata' => []];
        $result['rawdata'][] = $this->whoisClient->query->errstr[] = $this->whoisClient->query->query.' domain is not supported';
        $this->checkDns($result);
        WhoisClient::fixResult($result, $this->whoisClient->query->query);

        return $result;
    }

    /**
     * Get nameservers if missing.
     */
    public function checkDns(array &$result): void
    {
        if ($this->deepWhois && empty($result['regrinfo']['domain']['nserver'])) {
            $ns = @\dns_get_record($this->whoisClient->query->query, \DNS_NS);
            if (!\is_array($ns)) {
                return;
            }
            $nserver = [];
            foreach ($ns as $row) {
                $nserver[] = $row['target'];
            }
            if (\count($nserver) > 0) {
                $result['regrinfo']['domain']['nserver'] = WhoisClient::fixNameServer($nserver);
            }
        }
    }

    /**
     *  Fix and/or add name server information.
     */
    public function fixResult(array &$result, string $domain): void
    {
        // Add usual fields
        $result['regrinfo']['domain']['name'] = $domain;

        // Check if nameservers exist
        if (!isset($result['regrinfo']['registered'])) {
            if (\checkdnsrr($domain, 'NS')) {
                $result['regrinfo']['registered'] = 'yes';
            } else {
                $result['regrinfo']['registered'] = 'unknown';
            }
        }

        // Normalize nameserver fields
        if (isset($result['regrinfo']['domain']['nserver'])) {
            if (!\is_array($result['regrinfo']['domain']['nserver'])) {
                unset($result['regrinfo']['domain']['nserver']);
            } else {
                $result['regrinfo']['domain']['nserver'] = WhoisClient::fixNameServer($result['regrinfo']['domain']['nserver']);
            }
        }
    }

    /**
     * Guess query type.
     *
     * @return int Query type
     */
    private function getQueryType(string $query): int
    {
        $ipTools = new IpTools();

        if ($ipTools->validIpv4($query, false)) {
            if ($ipTools->validIpv4($query, true)) {
                return self::QTYPE_IPV4;
            }

            return self::QTYPE_UNKNOWN;
        }

        if ($ipTools->validIpv6($query, false)) {
            if ($ipTools->validIpv6($query, true)) {
                return self::QTYPE_IPV6;
            }

            return self::QTYPE_UNKNOWN;
        }

        if (!empty($query) && \str_contains($query, '.')) {
            return self::QTYPE_DOMAIN;
        }

        if (!empty($query) && !\str_contains($query, '.')) {
            return self::QTYPE_AS;
        }

        return self::QTYPE_UNKNOWN;
    }

    /**
     * Get nice HTML output.
     */
    public static function showHTML(array $result, ?string $useLink = null, string $params = 'query=$0'): string
    {
        // adds links for HTML output

        $email_regex = '/([-_\w\.]+)(@)([-_\w\.]+)\b/i';
        $html_regex = '/(?:^|\b)((((http|https|ftp):\/\/)|(www\.))([\w\.]+)([,:%#&\/?~=\w+\.-]+))(?:\b|$)/is';
        $ip_regex = '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/i';

        $out = '';
        $lempty = true;

        foreach ($result['rawdata'] as $line) {
            $line = \trim($line);

            if ('' === $line) {
                if ($lempty) {
                    continue;
                }
                $lempty = true;
            } else {
                $lempty = false;
            }

            $out .= $line."\n";
        }

        if ($lempty) {
            $out = \trim($out);
        }

        $out = \strip_tags($out);
        $out = \preg_replace($email_regex, '<a href="mailto:$0">$0</a>', $out);
        $out = \preg_replace_callback($html_regex, static function (array $matches): string {
            $web = $matches[0];
            if (\str_starts_with($matches[0], 'www.')) {
                $url = 'http://'.$web;
            } else {
                $url = $web;
            }

            return '<a href="'.$url.'" target="_blank">'.$web.'</a>';
        }, $out);

        if ($useLink) {
            $link = $useLink.'?'.$params;

            if (!\str_contains($out, '<a href=')) {
                $out = \preg_replace($ip_regex, '<a href="'.$link.'">$0</a>', $out);
            }

            if (isset($result['regrinfo']['domain']['nserver'])) {
                $nserver = $result['regrinfo']['domain']['nserver'];
            } else {
                $nserver = false;
            }

            if (isset($result['regrinfo']['network']['nserver'])) {
                $nserver = $result['regrinfo']['network']['nserver'];
            }

            if (\is_array($nserver)) {
                foreach ($nserver as $host => $ip) {
                    $url = '<a href="'.\str_replace('$0', $ip, $link).'">'.$host.'</a>';
                    $out = \str_ireplace($host, $url, $out);
                }
            }
        }

        // Add bold field names
        $out = \preg_replace("/(?m)^([-\s\.&;'\w\t\(\)\/]+:\s*)/", '<b>$1</b>', $out);

        // Add italics for disclaimer
        $out = \preg_replace('/(?m)^(%.*)/', '<i>$0</i>', $out);

        return \trim(\nl2br($out));
    }
}
