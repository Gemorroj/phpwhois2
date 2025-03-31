<?php

namespace PHPWhois2;

use Algo26\IdnaConvert\Exception\AlreadyPunycodeException;
use Algo26\IdnaConvert\Exception\InvalidCharacterException;
use Algo26\IdnaConvert\ToIdn;

class Whois
{
    private const QTYPE_UNKNOWN = 0;
    private const QTYPE_DOMAIN = 1;
    private const QTYPE_IPV4 = 2;
    private const QTYPE_IPV6 = 3;
    private const QTYPE_AS = 4;

    public function __construct(private readonly WhoisClient $whoisClient = new WhoisClient())
    {
    }

    /**
     *  Lookup query.
     *
     * @param string $query Domain name or other entity
     *
     * @throws InvalidCharacterException
     */
    public function lookup(string $query): Data
    {
        // start clean
        $this->whoisClient->queryParams->clear();

        $query = \trim($query);
        try {
            $query = (new ToIdn())->convert($query);
        } catch (AlreadyPunycodeException $e) {
            // $query is already a Punycode
        }

        // If domain to query was not set
        if (!$query) {
            return new Data();
        }

        // Set domain to query in query array
        $this->whoisClient->queryParams->query = $domain = $query = \strtolower($query);

        // Find a query type
        $qType = $this->getQueryType($query);

        switch ($qType) {
            case self::QTYPE_IPV4:
                // IPv4 Prepare to do lookup via the 'ip' handler
                $ip = @\gethostbyname($query);

                // default server
                $this->whoisClient->queryParams->server = 'whois.arin.net';
                $this->whoisClient->queryParams->args = "n $ip";
                $this->whoisClient->queryParams->handler = 'ip';
                $this->whoisClient->queryParams->hostIp = $ip;
                $this->whoisClient->queryParams->query = $ip;
                $this->whoisClient->queryParams->tld = 'ip';
                $hostName = @\gethostbyaddr($ip);
                if (false !== $hostName) {
                    $this->whoisClient->queryParams->hostName = $hostName;
                }

                return $this->whoisClient->getData();

            case self::QTYPE_IPV6:
                // IPv6 AS Prepare to do lookup via the 'ip' handler
                $ip = @\gethostbyname($query);

                $this->whoisClient->queryParams->server = 'whois.ripe.net';
                $this->whoisClient->queryParams->handler = 'ripe';
                $this->whoisClient->queryParams->query = $ip;
                $this->whoisClient->queryParams->tld = 'ip';

                return $this->whoisClient->getData();

            case self::QTYPE_AS:
                // AS Prepare to do lookup via the 'ip' handler
                $ip = @\gethostbyname($query);
                $this->whoisClient->queryParams->server = 'whois.arin.net';
                if (0 === \stripos($ip, 'as')) {
                    $as = \substr($ip, 2);
                } else {
                    $as = $ip;
                }
                $this->whoisClient->queryParams->args = "a $as";
                $this->whoisClient->queryParams->handler = 'ip';
                $this->whoisClient->queryParams->query = $ip;
                $this->whoisClient->queryParams->tld = 'as';

                return $this->whoisClient->getData();
        }

        // Build array of all possible tld's for that domain
        $tld = '';
        $server = '';
        $dp = \explode('.', $domain);
        $np = \count($dp) - 1;
        $tldTests = [];

        for ($i = 0; $i < $np; ++$i) {
            \array_shift($dp);
            $tldTests[] = \implode('.', $dp);
        }

        // Search the correct whois server
        foreach ($tldTests as $tld) {
            // Test if we know in advance that no whois server is
            // available for this domain and that we can get the
            // data via http or whois request
            if (isset($this->whoisClient->queryParams->tldWhoisServer[$tld])) {
                $val = $this->whoisClient->queryParams->tldWhoisServer[$tld];

                $domain = \substr($query, 0, -\strlen($tld) - 1);
                $val = \str_replace('{domain}', $domain, $val);
                $server = \str_replace('{tld}', $tld, $val);
                break;
            }
        }

        if (!$server) {
            foreach ($tldTests as $tld) {
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
            $this->whoisClient->queryParams->server = $server;
            $this->whoisClient->queryParams->tld = $tld;
            $this->whoisClient->queryParams->handler = 'common';

            foreach ($tldTests as $hTld) {
                // special handler exists for the tld ?
                if (isset($this->whoisClient->queryParams->tldData[$hTld])) {
                    $this->whoisClient->queryParams->handler = $this->whoisClient->queryParams->tldData[$hTld];
                    break;
                }
            }

            // Special parameters ?
            if (isset($this->whoisClient->queryParams->whoisServerParams[$server])) {
                $param = $this->whoisClient->queryParams->whoisServerParams[$server];
                $param = \str_replace(['$domain', '$tld'], [$domain, $tld], $param);
                $this->whoisClient->queryParams->server .= '?'.$param;
            }

            $result = $this->whoisClient->getData();
            $this->checkDns($result);

            return $result;
        }

        // If tld not known, and domain not in DNS, return error
        return $this->unknown();
    }

    /**
     * Unsupported domains.
     */
    private function unknown(): Data
    {
        $this->whoisClient->queryParams->server = null;
        $this->whoisClient->queryParams->status = 'error';
        $result = new Data();
        $result->errstr[] = $this->whoisClient->queryParams->errstr[] = $this->whoisClient->queryParams->query.' domain is not supported';
        $this->checkDns($result);
        WhoisClient::fixResult($result, $this->whoisClient->queryParams->query);

        return $result;
    }

    /**
     * Get nameservers if missing.
     */
    private function checkDns(Data $result): void
    {
        if (empty($result->regrinfo['domain']['nserver'])) {
            $ns = @\dns_get_record($this->whoisClient->queryParams->query, \DNS_NS);
            if (!\is_array($ns)) {
                return;
            }
            $nservers = [];
            foreach ($ns as $row) {
                $nservers[] = $row['target'];
            }
            if ($nservers) {
                $result->regrinfo['domain']['nserver'] = WhoisClient::fixNameServer($nservers);
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
    public static function showHTML(Data $result, ?string $useLink = null, string $params = 'query=$0'): string
    {
        // adds links for HTML output

        $email_regex = '/([-_\w\.]+)(@)([-_\w\.]+)\b/i';
        $html_regex = '/(?:^|\b)((((http|https|ftp):\/\/)|(www\.))([\w\.]+)([,:%#&\/?~=\w+\.-]+))(?:\b|$)/is';
        $ip_regex = '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/i';

        $out = '';
        $lempty = true;

        foreach ($result->rawData as $line) {
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

            $nserver = null;
            if (isset($result->regrinfo['domain']['nserver'])) {
                $nserver = $result->regrinfo['domain']['nserver'];
            }
            if (isset($result->regrinfo['network']['nserver'])) {
                $nserver = $result->regrinfo['network']['nserver'];
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
