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

/**
 * phpWhois basic class.
 *
 * This is the basic client class
 */
class WhoisClient
{
    /** @var bool Is recursion allowed? */
    public bool $gtldRecurse = false;

    /** @var int Default WHOIS port */
    public int $port = 43;

    /** @var int Maximum number of retries on connection failure */
    public int $retry = 0;

    /** @var int Time to wait between retries */
    public int $sleep = 2;

    /** @var int Read buffer size (0 == char by char) */
    public int $buffer = 1024;

    /** @var int Communications timeout */
    public int $stimeout = 10;

    public bool $deepWhois;

    /** @var string[] List of servers and handlers (loaded from servers.whois) */
    public array $DATA = [];

    /** @var string[] Non UTF-8 servers */
    public array $NON_UTF8 = [];

    /** @var string[] List of Whois servers with special parameters */
    public array $WHOIS_PARAM = [];

    /** @var string[] TLD's that have special whois servers or that can only be reached via HTTP */
    public array $WHOIS_SPECIAL = [];

    /** @var string[] Handled gTLD whois servers */
    public array $WHOIS_GTLD_HANDLER = [];

    /** @var array Array to contain all query publiciables */
    public array $query = [
        'tld' => '',
        'type' => 'domain',
        'query' => '',
        'status' => '',
        'server' => '',
        'errstr' => [],
    ];

    /**
     * Constructor function.
     */
    public function __construct()
    {
        // Load DATA array
        $servers = require __DIR__.'/whois.servers.php';

        $this->DATA = $servers['DATA'];
        $this->NON_UTF8 = $servers['NON_UTF8'];
        $this->WHOIS_PARAM = $servers['WHOIS_PARAM'];
        $this->WHOIS_SPECIAL = $servers['WHOIS_SPECIAL'];
        $this->WHOIS_GTLD_HANDLER = $servers['WHOIS_GTLD_HANDLER'];
    }

    /**
     * Perform lookup.
     *
     * @return array Raw response as array separated by "\n"
     */
    public function getRawData(string $query): array
    {
        $this->query['query'] = $query;

        // clear error description
        if (isset($this->query['errstr'])) {
            $this->query['errstr'] = [];
        }

        if (!isset($this->query['server'])) {
            $this->query['status'] = 'error';
            $this->query['errstr'][] = 'No server specified';

            return [];
        }

        // Check if protocol is http
        if (
            \str_starts_with($this->query['server'], 'http://')
            || \str_starts_with($this->query['server'], 'https://')
        ) {
            $output = $this->httpQuery();

            if (!$output) {
                $this->query['status'] = 'error';
                $this->query['errstr'][] = 'Connect failed to: '.$this->query['server'];

                return [];
            }

            $this->query['args'] = \substr(\strstr($this->query['server'], '?'), 1);
            $this->query['server'] = \strtok($this->query['server'], '?');

            if (\str_starts_with($this->query['server'], 'http://')) {
                $this->query['server_port'] = 80;
            } else {
                $this->query['server_port'] = 443;
            }
        } else {
            // Get args
            if (\strpos($this->query['server'], '?')) {
                $parts = \explode('?', $this->query['server']);
                $this->query['server'] = \trim($parts[0]);
                $query_args = \trim($parts[1]);

                // replace substitution parameters
                $query_args = \str_replace(['{query}', '{version}'], [$query, 'phpWhois'], $query_args);

                $iptools = new IpTools();
                if (\str_contains($query_args, '{ip}')) {
                    $query_args = \str_replace('{ip}', $iptools->getClientIp(), $query_args);
                }

                if (\str_contains($query_args, '{hname}')) {
                    $query_args = \str_replace('{hname}', \gethostbyaddr($iptools->getClientIp()), $query_args);
                }
            } else {
                if (empty($this->query['args'])) {
                    $query_args = $query;
                } else {
                    $query_args = $this->query['args'];
                }
            }

            $this->query['args'] = $query_args;

            if (\str_starts_with($this->query['server'], 'rwhois://')) {
                $this->query['server'] = \substr($this->query['server'], 9);
            }

            if (\str_starts_with($this->query['server'], 'whois://')) {
                $this->query['server'] = \substr($this->query['server'], 8);
            }

            // Get port
            if (\strpos($this->query['server'], ':')) {
                $parts = \explode(':', $this->query['server']);
                $this->query['server'] = \trim($parts[0]);
                $this->query['server_port'] = \trim($parts[1]);
            } else {
                $this->query['server_port'] = $this->port;
            }

            // Connect to whois server, or return if failed
            $ptr = $this->connect();

            if (false === $ptr) {
                $this->query['status'] = 'error';
                $this->query['errstr'][] = 'Connect failed to: '.$this->query['server'];

                return [];
            }

            \stream_set_timeout($ptr, $this->stimeout);
            \stream_set_blocking($ptr, 0);

            // Send query
            \fwrite($ptr, \trim($query_args)."\r\n");

            // Prepare to receive result
            $raw = '';
            $start = \time();
            $null = null;
            $r = [$ptr];

            while (!\feof($ptr)) {
                if (!empty($r) && \stream_select($r, $null, $null, $this->stimeout)) {
                    $raw .= \fgets($ptr, $this->buffer);
                }

                if (\time() - $start > $this->stimeout) {
                    $this->query['status'] = 'error';
                    $this->query['errstr'][] = 'Timeout reading from '.$this->query['server'];

                    return [];
                }
            }

            if (\array_key_exists($this->query['server'], $this->NON_UTF8)) {
                $raw = @\utf8_encode($raw);
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
     * @return array The *rawdata* element contains an
     *               array of lines gathered from the whois query. If a top level domain
     *               handler class was found for the domain, other elements will have been
     *               populated too.
     */
    public function getData(string $query = '', $deep_whois = true): array
    {
        // If domain to query passed in, use it, otherwise use domain from initialisation
        $query = !empty($query) ? $query : $this->query['query'];

        $output = $this->getRawData($query);

        // Create result and set 'rawdata'
        $result = ['rawdata' => $output];
        $result = $this->setWhoisInfo($result);

        // Return now on error
        if (empty($output)) {
            return $result;
        }

        // If we have a handler, post-process it with it
        if (isset($this->query['handler'])) {
            // Keep server list
            $servers = $result['regyinfo']['servers'];
            unset($result['regyinfo']['servers']);

            // Process data
            $result = $this->process($result, $deep_whois);

            // Add new servers to the server list
            if (isset($result['regyinfo']['servers'])) {
                $result['regyinfo']['servers'] = \array_merge($servers, $result['regyinfo']['servers']);
            } else {
                $result['regyinfo']['servers'] = $servers;
            }

            // Handler may forget to set rawdata
            if (!isset($result['rawdata'])) {
                $result['rawdata'] = $output;
            }
        }

        // Type defaults to domain
        if (!isset($result['regyinfo']['type'])) {
            $result['regyinfo']['type'] = 'domain';
        }

        // Add error information if any
        if (isset($this->query['errstr'])) {
            $result['errstr'] = $this->query['errstr'];
        }

        // Fix/add nameserver information
        if ('ip' !== $this->query['tld'] && \method_exists($this, 'fixResult')) {
            $this->fixResult($result, $query);
        }

        return $result;
    }

    /**
     * Adds whois server query information to result.
     *
     * @param array $result Result array
     *
     * @return array Original result array with server query information
     */
    public function setWhoisInfo(array $result): array
    {
        $info = [
            'server' => $this->query['server'],
        ];

        if (!empty($this->query['args'])) {
            $info['args'] = $this->query['args'];
        } else {
            $info['args'] = $this->query['query'];
        }

        if (!empty($this->query['server_port'])) {
            $info['port'] = $this->query['server_port'];
        } else {
            $info['port'] = 43;
        }

        if (isset($result['regyinfo']['whois'])) {
            unset($result['regyinfo']['whois']);
        }

        if (isset($result['regyinfo']['rwhois'])) {
            unset($result['regyinfo']['rwhois']);
        }

        $result['regyinfo']['servers'][] = $info;

        return $result;
    }

    /**
     * Convert html output to plain text.
     *
     * @return array Rawdata
     */
    private function httpQuery(): array
    {
        $lines = @\file($this->query['server']);

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
     * @param string|null $server Server address to connect. If null, $this->query['server'] will be used
     *
     * @return resource|false Returns a socket connection pointer on success, or -1 on failure
     */
    public function connect(?string $server = null)
    {
        if (empty($server)) {
            $server = $this->query['server'];
        }

        /* @TODO Throw an exception here */
        if (empty($server)) {
            return false;
        }

        $port = $this->query['server_port'];

        $parsed = $this->parseServer($server);
        $server = $parsed['host'];

        if (\array_key_exists('port', $parsed)) {
            $port = $parsed['port'];
        }

        // Enter connection attempt loop
        $retry = 0;

        while ($retry <= $this->retry) {
            // Set query status
            $this->query['status'] = 'ready';

            // Connect to whois port
            $ptr = @\fsockopen($server, $port, $errno, $errstr, $this->stimeout);

            if ($ptr > 0) {
                $this->query['status'] = 'ok';

                return $ptr;
            }

            // Failed this attempt
            $this->query['status'] = 'error';
            $this->query['error'][] = "[$errno] $errstr";
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
     * @return array On success, returns the result from the handler.
     *               On failure, returns passed result unaltered.
     */
    public function process(array &$result, bool $deep_whois = true): array
    {
        $handlerName = $this->loadHandler($this->query['handler']);

        if (null === $handlerName) {
            $handlerName = $this->loadLegacyHandler($this->query['handler'], $this->query['file']);
        }

        if (null === $handlerName) {
            $this->query['errstr'][] = "Can't find {$this->query['handler']} handler: ".$this->query['file'];

            return $result;
        }

        if (!$this->gtldRecurse && 'whois.gtld.php' === $this->query['file']) {
            return $result;
        }

        // Pass result to handler
        $handler = new $handlerName('');

        // If handler returned an error, append it to the query errors list
        if (isset($handler->query['errstr'])) {
            $this->query['errstr'][] = $handler->query['errstr'];
        }

        $handler->deepWhois = $deep_whois;

        // Process and return the result
        return $handler->parse($result, $this->query['query']);
    }

    /**
     * Does more (deeper) whois.
     *
     * @return array Resulting array
     */
    public function deepWhois(string $query, array $result): array
    {
        if (!isset($result['regyinfo']['whois'])) {
            return $result;
        }

        $this->query['server'] = $wserver = $result['regyinfo']['whois'];
        unset($result['regyinfo']['whois']);
        $subresult = $this->getRawData($query);

        if (!empty($subresult)) {
            $result = $this->setWhoisInfo($result);
            $result['rawdata'] = $subresult;

            if (isset($this->WHOIS_GTLD_HANDLER[$wserver])) {
                $this->query['handler'] = $this->WHOIS_GTLD_HANDLER[$wserver];
            } else {
                $parts = \explode('.', $wserver);
                $hname = \strtolower($parts[1]);

                if (($fp = @\fopen('whois.gtld.'.$hname.'.php', 'r', 1)) && \fclose($fp)) {
                    $this->query['handler'] = $hname;
                }
            }

            if (!empty($this->query['handler'])) {
                $this->query['file'] = \sprintf('whois.gtld.%s.php', $this->query['handler']);
                $regrinfo = $this->process($subresult); // $result['rawdata']);
                $result['regrinfo'] = $this->mergeResults($result['regrinfo'], $regrinfo);
            }
        }

        return $result;
    }

    /**
     * Merge results.
     */
    public function mergeResults(array $a1, array $a2): array
    {
        \reset($a2);

        foreach ($a2 as $key => $val) {
            if (isset($a1[$key])) {
                if (\is_array($val)) {
                    if ('nserver' !== $key) {
                        $a1[$key] = $this->mergeResults($a1[$key], $val);
                    }
                } else {
                    $val = \trim($val);
                    if ('' !== $val) {
                        $a1[$key] = $val;
                    }
                }
            } else {
                $a1[$key] = $val;
            }
        }

        return $a1;
    }

    /**
     * Remove unnecessary symbols from nameserver received from whois server.
     *
     * @param string[] $nserver List of received nameservers
     *
     * @return string[]
     */
    public function fixNameServer(array $nserver): array
    {
        $dns = [];

        foreach ($nserver as $val) {
            $val = \str_replace(['[', ']', '(', ')', "\t"], ['', '', '', '', ' '], \trim($val));
            $parts = \explode(' ', $val);
            $host = '';
            $ip = '';

            foreach ($parts as $p) {
                if ('.' === \substr($p, -1)) {
                    $p = \substr($p, 0, -1);
                }

                if ((-1 == \ip2long($p)) || (false === \ip2long($p))) {
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

            if ('.' === $host[\strlen($host) - 1]) {
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
     * @return array Array containing 'host' key with server host and 'port' if defined in original $server string
     */
    public function parseServer(string $server): array
    {
        $server = \trim($server);

        $server = \preg_replace('/\/$/', '', $server);
        $ipTools = new IpTools();
        if ($ipTools->validIpv6($server)) {
            $result = ['host' => "[$server]"];
        } else {
            $parsed = \parse_url($server);
            if (\array_key_exists('path', $parsed) && !\array_key_exists('host', $parsed)) {
                $host = \preg_replace('/\//', '', $parsed['path']);

                // if host is ipv6 with port. Example: [1a80:1f45::ebb:12]:8080
                if (\preg_match('/^(\[[a-f0-9:]+\]):(\d{1,5})$/i', $host, $matches)) {
                    $result = ['host' => $matches[1], 'port' => $matches[2]];
                } else {
                    $result = ['host' => $host];
                }
            } else {
                $result = $parsed;
            }
        }

        return $result;
    }

    protected function loadHandler(string $queryHandler): ?string
    {
        $queryHandler = \ucfirst($queryHandler);
        $handlerName = "phpWhois\\Handlers\\{$queryHandler}Handler";
        if (\class_exists($handlerName)) {
            return $handlerName;
        }

        return null;
    }

    protected function loadLegacyHandler(string $queryHandler, string $queryFile): ?string
    {
        $handler_name = \str_replace('.', '_', $queryHandler);

        // If the handler has not already been included somehow, include it now
        $HANDLER_FLAG = \sprintf('__%s_HANDLER__', \strtoupper($handler_name));

        if (!\defined($HANDLER_FLAG)) {
            include $queryFile;
        }

        // If the handler has still not been included, append to query errors list and return
        if (!\defined($HANDLER_FLAG)) {
            return null;
        }

        return $handler_name.'_handler';
    }
}
