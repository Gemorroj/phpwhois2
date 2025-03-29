#!/usr/bin/env php
<?php
/**
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
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
 * @link      http://phpwhois.pw
 * @copyright Copyright (C)1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @copyright Maintained by David Saez
 * @copyright Copyright (c) 2014 Dmitry Lukashin
 */

use PHPWhois2\Whois;

if (!\is_dir(\dirname(__DIR__) . '/vendor')) {
    throw new \LogicException('Dependencies are missing. Try running "composer install".');
}

require_once __DIR__ . '/../vendor/autoload.php';

$fixturePath = __DIR__ . '/../tests/fixtures/';

// Read domain list to test
$rows = \json_decode(\file_get_contents(__DIR__ . '/update-fixtures.json'), true, 512, \JSON_THROW_ON_ERROR);

// Specific test by TLD or key
if (!empty($argv[1]) && isset($rows[$argv[1]])) {
    $rows = [$rows[$argv[1]]];
}

// Test domains
$whois = new Whois();

\stream_set_write_buffer(STDIN, 0);

foreach ($rows as $key => $domains) {
    $domainCount = \count($domains);

    echo "\n --- [ $key => $domainCount domains ] --- \n";

    foreach ($domains as $index => $domain) {
        try {
            ++$index;
            echo "[$index/$domainCount] Creating fixture for $domain \n";
            $lookup = $whois->lookup($domain);
            $result = \implode(\PHP_EOL, $lookup['rawdata']);

            $safeDomain = \makePathSafe($domain);
            $write = \file_put_contents("{$fixturePath}/{$safeDomain}.txt", $result);
            if (false === $write) {
                throw new \RuntimeException("Can't write to file {$fixturePath}/{$safeDomain}.txt. Check permissions.");
            }
        } catch (\Exception $exception) {
            echo "  Exception: {$exception->getMessage()}\n";
        } catch (\Error $error) {
            echo "  Error: {$error->getMessage()}\n";
        }
    }
}

function makePathSafe(string $filename): string
{
    return \str_replace(':', '-', $filename);
}

// sha1("this-domain-is-not-registered");
