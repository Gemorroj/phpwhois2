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
 * Utilities for parsing ip addresses.
 */
final class IpTools
{
    public const IP_TYPE_ANY = 0;
    public const IP_TYPE_IPv4 = 1;
    public const IP_TYPE_IPv6 = 2;

    /**
     * Check if ip address is valid.
     *
     * @param string $ip     IP address for validation
     * @param int    $type   Type of ip address. Possible value are: IpTools::IP_TYPE_*
     * @param bool   $strict If true - fail validation on reserved and private ip ranges
     *
     * @return bool True if ip is valid. False otherwise
     */
    public function validIp(string $ip, int $type = self::IP_TYPE_ANY, bool $strict = true): bool
    {
        return match ($type) {
            self::IP_TYPE_ANY => $this->validIpv4($ip, $strict) || $this->validIpv6($ip, $strict),
            self::IP_TYPE_IPv4 => $this->validIpv4($ip, $strict),
            self::IP_TYPE_IPv6 => $this->validIpv6($ip, $strict),
            default => false,
        };
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
