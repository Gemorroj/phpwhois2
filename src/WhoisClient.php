<?php

namespace PHPWhois2;

use PHPWhois2\Handler\AbstractHandler;

class WhoisClient
{
    /** @var int Default WHOIS port */
    protected int $port = 43;

    /** @var int Maximum number of retries on connection failure */
    protected int $retry = 0;

    /** @var int Time to wait between retries */
    protected int $sleep = 2;

    /** @var int Read buffer size (0 == char by char) */
    protected int $buffer = 1024;

    /** @var int Communications timeout */
    protected int $timeout = 10;

    public function __construct(public readonly QueryParams $queryParams = new QueryParams())
    {
    }

    /**
     * Perform lookup.
     *
     * @return array Raw response as array separated by "\n"
     */
    public function getRawData(string $query): array
    {
        $this->queryParams->query = $query;

        // clear error description
        $this->queryParams->errstr = [];

        if (!$this->queryParams->server) {
            $this->queryParams->status = 'error';
            $this->queryParams->errstr[] = 'No server specified';

            return [];
        }

        // Check if protocol is http
        if (\str_starts_with($this->queryParams->server, 'http://') || \str_starts_with($this->queryParams->server, 'https://')) {
            $output = $this->httpQuery();

            if (!$output) {
                $this->queryParams->status = 'error';
                $this->queryParams->errstr[] = 'Connect failed to: '.$this->queryParams->server;

                return [];
            }

            $this->queryParams->args = \substr(\strstr($this->queryParams->server, '?'), 1);
            $this->queryParams->server = \strtok($this->queryParams->server, '?');

            if (\str_starts_with($this->queryParams->server, 'http://')) {
                $this->queryParams->serverPort = 80;
            } else {
                $this->queryParams->serverPort = 443;
            }
        } else {
            // Get args
            if (\strpos($this->queryParams->server, '?')) {
                $parts = \explode('?', $this->queryParams->server);
                $this->queryParams->server = \trim($parts[0]);
                $queryArgs = \trim($parts[1]);

                // replace substitution parameters
                $queryArgs = \str_replace(['{query}', '{version}'], [$query, 'PHPWhois2'], $queryArgs);

                $iptools = new IpTools();
                if (\str_contains($queryArgs, '{ip}')) {
                    $queryArgs = \str_replace('{ip}', $iptools->getClientIp(), $queryArgs);
                }

                if (\str_contains($queryArgs, '{hname}')) {
                    $queryArgs = \str_replace('{hname}', \gethostbyaddr($iptools->getClientIp()), $queryArgs);
                }
            } else {
                if (empty($this->queryParams->args)) {
                    $queryArgs = $query;
                } else {
                    $queryArgs = $this->queryParams->args;
                }
            }

            $this->queryParams->args = $queryArgs;

            if (\str_starts_with($this->queryParams->server, 'rwhois://')) {
                $this->queryParams->server = \substr($this->queryParams->server, 9);
            }

            if (\str_starts_with($this->queryParams->server, 'whois://')) {
                $this->queryParams->server = \substr($this->queryParams->server, 8);
            }

            // Get port
            if (\strpos($this->queryParams->server, ':')) {
                $parts = \explode(':', $this->queryParams->server);
                $this->queryParams->server = \trim($parts[0]);
                $this->queryParams->serverPort = (int) \trim($parts[1]);
            } else {
                $this->queryParams->serverPort = $this->port;
            }

            // Connect to whois server, or return if failed
            $ptr = $this->connect();

            if (false === $ptr) {
                $this->queryParams->status = 'error';
                $this->queryParams->errstr[] = 'Connect failed to: '.$this->queryParams->server;

                return [];
            }

            \stream_set_timeout($ptr, $this->timeout);
            \stream_set_blocking($ptr, 0);

            // Send query
            \fwrite($ptr, \trim($queryArgs)."\r\n");

            // Prepare to receive result
            $raw = '';
            $start = \time();
            $null = null;
            $r = [$ptr];

            while (!\feof($ptr)) {
                if (!empty($r) && \stream_select($r, $null, $null, $this->timeout)) {
                    $raw .= \fgets($ptr, $this->buffer);
                }

                if (\time() - $start > $this->timeout) {
                    $this->queryParams->status = 'error';
                    $this->queryParams->errstr[] = 'Timeout reading from '.$this->queryParams->server;

                    return [];
                }
            }

            if (isset($this->queryParams->nonUtf8Servers[$this->queryParams->server])) {
                $raw = \mb_convert_encoding($raw, 'UTF-8', 'ISO-8859-1');
            }

            $output = \explode("\n", $raw);

            // Drop empty last line (if it's empty! - saleck)
            if (empty($output[\count($output) - 1])) {
                unset($output[\count($output) - 1]);
            }
        }

        return $output;
    }

    /**
     * Perform lookup.
     *
     * @return Data The *rawdata* element contains an
     *              array of lines gathered from the whois query. If a top level domain
     *              handler class was found for the domain, other elements will have been
     *              populated too.
     */
    public function getData(): Data
    {
        $output = $this->getRawData($this->queryParams->query);

        // Create result and set 'rawdata'
        $result = new Data();
        $result->rawData = $output;
        $this->makeWhoisInfo($result);

        // Return now on error
        if (!$output) {
            return $result;
        }

        // If we have a handler, post-process it with it
        if ($this->queryParams->handler) {
            // Keep server list
            $servers = $result->regyinfo['servers'];
            // Process data
            $result = $this->process($output);

            // Add new servers to the server list
            if (isset($result->regyinfo['servers'])) {
                $result->regyinfo['servers'] = \array_merge($servers, $result->regyinfo['servers']);
            } else {
                $result->regyinfo['servers'] = $servers;
            }
        }

        // Type defaults to domain
        if (!isset($result->regyinfo['type'])) {
            $result->regyinfo['type'] = 'domain';
        }

        // Add error information if any
        if ($this->queryParams->errstr) {
            $result->errstr = $this->queryParams->errstr;
        }

        // Fix/add nameserver information
        if ('ip' !== $this->queryParams->tld) {
            self::fixResult($result, $this->queryParams->query);
        }

        return $result;
    }

    public static function fixResult(Data $result, string $domain): void
    {
        // Add usual fields
        $result->regrinfo['domain']['name'] = $domain;

        // Check if nameservers exist
        if (!isset($result->regrinfo['registered'])) {
            if (\checkdnsrr($domain, 'NS')) {
                $result->regrinfo['registered'] = 'yes';
            } else {
                $result->regrinfo['registered'] = 'unknown';
            }
        }

        // Normalize nameserver fields
        if (isset($result->regrinfo['domain']['nserver'])) {
            if (!\is_array($result->regrinfo['domain']['nserver'])) {
                unset($result->regrinfo['domain']['nserver']);
            } else {
                $result->regrinfo['domain']['nserver'] = self::fixNameServer($result->regrinfo['domain']['nserver']);
            }
        }
    }

    /**
     * Adds whois server query information to result.
     *
     * @param Data $result Result data
     */
    public function makeWhoisInfo(Data $result): void
    {
        $info = [
            'server' => $this->queryParams->server,
        ];

        if (!empty($this->queryParams->args)) {
            $info['args'] = $this->queryParams->args;
        } else {
            $info['args'] = $this->queryParams->query;
        }

        if (!empty($this->queryParams->serverPort)) {
            $info['port'] = $this->queryParams->serverPort;
        } else {
            $info['port'] = 43;
        }

        if (isset($result->regyinfo['whois'])) {
            unset($result->regyinfo['whois']);
        }

        if (isset($result->regyinfo['rwhois'])) {
            unset($result->regyinfo['rwhois']);
        }

        $result->regyinfo['servers'][] = $info;
    }

    /**
     * Convert html output to plain text.
     *
     * @return array Rawdata
     */
    protected function httpQuery(): array
    {
        $lines = @\file($this->queryParams->server);

        if (!$lines) {
            return [];
        }

        $output = '';
        $pre = '';

        foreach ($lines as $val) {
            $val = \trim($val);

            $pos = \stripos($val, '<PRE>');
            if (false !== $pos) {
                $pre = "\n";
                $output .= \substr($val, 0, $pos)."\n";
                $val = \substr($val, $pos + 5);
            }
            $pos = \stripos($val, '</PRE>');
            if (false !== $pos) {
                $pre = '';
                $output .= \substr($val, 0, $pos)."\n";
                $val = \substr($val, $pos + 6);
            }
            $output .= $val.$pre;
        }

        $search = [
            '<BR>', '<P>', '</TITLE>',
            '</H1>', '</H2>', '</H3>',
            '<br>', '<p>', '</title>',
            '</h1>', '</h2>', '</h3>'];

        $output = \str_replace($search, "\n", $output);
        $output = \str_replace(['<TD', '<td', '<tr', '<TR', '&nbsp;'], [' <td', ' <td', "\n<tr", "\n<tr", ' '], $output);
        $output = \strip_tags($output);
        $output = \explode("\n", $output);

        $rawdata = [];
        $null = 0;

        foreach ($output as $val) {
            $val = \trim($val);
            if ('' == $val) {
                if (++$null > 2) {
                    continue;
                }
            } else {
                $null = 0;
            }
            $rawdata[] = $val;
        }

        return $rawdata;
    }

    /**
     * Open a socket to the whois server.
     *
     * @return resource|false Returns a socket connection pointer on success, or -1 on failure
     */
    protected function connect()
    {
        $server = $this->queryParams->server;

        /* @TODO Throw an exception here */
        if (empty($server)) {
            return false;
        }

        $port = $this->queryParams->serverPort;

        $parsed = $this->parseServer($server);
        $server = $parsed['host'];

        if ($parsed['port']) {
            $port = $parsed['port'];
        }

        // Enter connection attempt loop
        $retry = 0;

        while ($retry <= $this->retry) {
            // Set query status
            $this->queryParams->status = 'ready';

            // Connect to whois port
            $ptr = @\fsockopen($server, $port, $errno, $errstr, $this->timeout);

            if ($ptr > 0) {
                $this->queryParams->status = 'ok';

                return $ptr;
            }

            // Failed this attempt
            $this->queryParams->status = 'error';
            $this->queryParams->errstr[] = "[$errno] $errstr";
            ++$retry;

            // Sleep before retrying
            \sleep($this->sleep);
        }

        // If we get this far, it hasn't worked
        return false;
    }

    /**
     * Post-process result with handler class.
     *
     * @return Data On success, returns the result from the handler.
     *              On failure, returns passed result unaltered.
     */
    public function process(array $rawData): Data
    {
        $handler = $this->loadHandler();

        // Process and return the result
        return $handler->parse($rawData, $this->queryParams->query);
    }

    /**
     * Remove unnecessary symbols from nameserver received from whois server.
     *
     * @param string[] $nservers List of received nameservers
     *
     * @return string[]
     */
    public static function fixNameServer(array $nservers): array
    {
        $dns = [];

        foreach ($nservers as $val) {
            $val = \str_replace(['[', ']', '(', ')', "\t"], ['', '', '', '', ' '], \trim($val));
            $parts = \explode(' ', $val);
            $host = '';
            $ip = '';

            foreach ($parts as $p) {
                if (\str_ends_with($p, '.')) {
                    $p = \substr($p, 0, -1);
                }

                $ip2long = \ip2long($p);
                if (-1 === $ip2long || false === $ip2long) {
                    // Hostname ?
                    if ('' == $host && \preg_match('/^[\w\-]+(\.[\w\-]+)+$/', $p)) {
                        $host = $p;
                    }
                } else {
                    // IP Address
                    $ip = $p;
                }
            }

            // Valid host name ?
            if ('' == $host) {
                continue;
            }

            // Get ip address
            if ('' == $ip) {
                $ip = \gethostbyname($host);
                if ($ip == $host) {
                    $ip = '(DOES NOT EXIST)';
                }
            }

            if (\str_ends_with($host, '.')) {
                $host = \substr($host, 0, -1);
            }

            $dns[\strtolower($host)] = $ip;
        }

        return $dns;
    }

    /**
     * Parse server string into array with host and port keys.
     *
     * @param string $server server string in various formattes
     *
     * @return array{host: string, port: int|null} Array containing 'host' key with server host and 'port' if defined in original $server string
     */
    protected function parseServer(string $server): array
    {
        $server = \trim($server);

        $server = \preg_replace('/\/$/', '', $server);
        $ipTools = new IpTools();
        if ($ipTools->validIpv6($server)) {
            $result = ['host' => "[$server]", 'port' => null];
        } else {
            $parsed = \parse_url($server);
            if (\array_key_exists('path', $parsed) && !\array_key_exists('host', $parsed)) {
                $host = \preg_replace('/\//', '', $parsed['path']);

                // if host is ipv6 with port. Example: [1a80:1f45::ebb:12]:8080
                if (\preg_match('/^(\[[a-f0-9:]+\]):(\d{1,5})$/i', $host, $matches)) {
                    $result = ['host' => $matches[1], 'port' => (int) $matches[2]];
                } else {
                    $result = ['host' => $host, 'port' => null];
                }
            } else {
                $result = $parsed;
            }
        }

        return $result;
    }

    protected function loadHandler(): AbstractHandler
    {
        if (!$this->queryParams->handler) {
            throw new \RuntimeException('Unable to load handler.');
        }

        $queryHandler = \ucfirst($this->queryParams->handler);

        $handlerName = "PHPWhois2\\Handler\\{$queryHandler}Handler";
        if (\class_exists($handlerName)) {
            return new $handlerName($this);
        }

        $handlerNameGtld = "PHPWhois2\\Handler\\Gtld\\{$queryHandler}Handler";
        if (\class_exists($handlerNameGtld)) {
            return new $handlerNameGtld($this);
        }

        $handlerNameIp = "PHPWhois2\\Handler\\Ip\\{$queryHandler}Handler";
        if (\class_exists($handlerNameIp)) {
            return new $handlerNameIp($this);
        }

        $handlerNameCustom = "PHPWhois2\\Handler\\Custom\\{$queryHandler}Handler";
        if (\class_exists($handlerNameCustom)) {
            return new $handlerNameCustom($this);
        }

        throw new \RuntimeException('Cannot load handler "'.$queryHandler.'".');
    }
}
