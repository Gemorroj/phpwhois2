<?php

namespace PHPWhois2;

final class IpTools
{
    /**
     * Check if ip address is valid.
     *
     * @param string $ip     IP address for validation
     * @param bool   $strict If true - fail validation on reserved and private ip ranges
     *
     * @return bool True if ip is valid. False otherwise
     */
    public function validIp(string $ip, bool $strict = true): bool
    {
        return $this->validIpv4($ip, $strict) || $this->validIpv6($ip, $strict);
    }

    /**
     * Check if given IP is valid ipv4 address and doesn't belong to private and
     * reserved ranges.
     *
     * @param string $ip     Ip address
     * @param bool   $strict If true - fail validation on reserved and private ip ranges
     */
    public function validIpv4(string $ip, bool $strict = true): bool
    {
        $flags = \FILTER_FLAG_IPV4;
        if ($strict) {
            $flags = \FILTER_FLAG_IPV4 | \FILTER_FLAG_NO_PRIV_RANGE | \FILTER_FLAG_NO_RES_RANGE;
        }

        return false !== \filter_var($ip, \FILTER_VALIDATE_IP, ['flags' => $flags]);
    }

    /**
     * Check if given IP is valid ipv6 address and doesn't belong to private ranges.
     *
     * @param string $ip     Ip address
     * @param bool   $strict If true - fail validation on reserved and private ip ranges
     */
    public function validIpv6(string $ip, bool $strict = true): bool
    {
        $flags = \FILTER_FLAG_IPV6;
        if ($strict) {
            $flags = \FILTER_FLAG_IPV6 | \FILTER_FLAG_NO_PRIV_RANGE;
        }

        return false !== \filter_var($ip, \FILTER_VALIDATE_IP, ['flags' => $flags]);
    }

    /**
     * Try to get real IP from client web request.
     */
    public function getClientIp(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validIp($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            foreach (\explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $ip) {
                if ($this->validIp(\trim($ip))) {
                    return \trim($ip);
                }
            }
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validIp($_SERVER['HTTP_X_FORWARDED'])) {
            return $_SERVER['HTTP_X_FORWARDED'];
        }

        if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validIp($_SERVER['HTTP_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_FORWARDED_FOR'];
        }

        if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validIp($_SERVER['HTTP_FORWARDED'])) {
            return $_SERVER['HTTP_FORWARDED'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Convert CIDR to net range.
     *
     * @TODO provide example
     *
     * @noinspection TypeUnsafeComparisonInspection
     */
    public function cidrConv(string $net): string
    {
        $start = \strtok($net, '/');
        $n = 3 - \substr_count($net, '.');

        if ($n > 0) {
            for ($i = $n; $i > 0; --$i) {
                $start .= '.0';
            }
        }

        $bits1 = \str_pad(\decbin(\ip2long($start)), 32, '0', 'STR_PAD_LEFT');
        $net = 2 ** (32 - \substr(\strstr($net, '/'), 1)) - 1;
        $bits2 = \str_pad(\decbin($net), 32, '0', 'STR_PAD_LEFT');
        $final = '';

        for ($i = 0; $i < 32; ++$i) {
            if ($bits1[$i] == $bits2[$i]) {
                $final .= $bits1[$i];
            }
            if (1 == $bits1[$i] && 0 == $bits2[$i]) {
                $final .= $bits1[$i];
            }
            if (0 == $bits1[$i] && 1 == $bits2[$i]) {
                $final .= $bits2[$i];
            }
        }

        return $start.' - '.\long2ip(\bindec($final));
    }
}
