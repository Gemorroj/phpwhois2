<?php

namespace PHPWhois2\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;
use PHPWhois2\WhoisClient;

final class ParserTest extends TestCase
{
    private const RAW_DATA = [
        "Domain: \${field}\n",
        "Registrar:\n",
        "\tName:\t \${field}\n",
        "fax: \${field}\n",
        "Registrant Name: \${k}\n",
    ];

    public static function dateProvider(): array
    {
        return [
            'nic.ag' => [
                'date' => '1998-05-02T04:00:00Z',
                'format' => 'ymd',
                'expected' => '1998-05-02',
            ],
            'nic.at' => [
                'date' => '20121116 16:58:21',
                'format' => 'Ymd',
                'expected' => '2012-11-16',
            ],
            'telstra.com.au' => [
                'date' => '11-May-2016 05:18:45 UTC',
                'format' => 'mdy',
                'expected' => '2016-05-11',
            ],
            'registro.br' => [
                'date' => '19971217',
                'format' => 'Ymd',
                'expected' => '1997-12-17',
            ],
            'registro.br-2' => [
                'date' => '19990221 #142485',
                'format' => 'Ymd',
                'expected' => '1999-02-21',
            ],
            'cira.ca' => [
                'date' => '1998/02/05',
                'format' => 'Ymd',
                'expected' => '1998-02-05',
            ],
            'nic.co' => [
                'date' => '2010-04-23T09:12:48Z',
                'format' => 'mdy',
                'expected' => '2010-04-23',
            ],
            'day smaller than month' => [
                'date' => '2010-06-02T01:32:58Z',
                'format' => 'ymd',
                'expected' => '2010-06-02',
            ],
            'nic.cz' => [
                'date' => '06.03.2002 18:11:00',
                'format' => 'dmy',
                'expected' => '2002-03-06',
            ],
            'nic.cz-2' => [
                'date' => '15.03.2027 18:11:00',
                'format' => 'dmy',
                'expected' => '2027-03-15',
            ],
            'nic.fr' => [
                'date' => '23/08/2005 hostmaster@nic.fr',
                'format' => 'dmY',
                'expected' => '2005-08-23',
            ],
            'nic.hu' => [
                'date' => '1996.06.27 13:36:21',
                'format' => 'ymd',
                'expected' => '1996-06-27',
            ],
            'domainregistry.ie' => [
                'date' => '01-January-2025',
                'format' => 'Ymd',
                'expected' => '2025-01-01',
            ],
            'isnic.is' => [
                'date' => 'November  6 2000',
                'format' => 'mdy',
                'expected' => '2000-11-06',
            ],
            'dns.lu' => [
                'date' => '31/05/1995',
                'format' => 'dmy',
                'expected' => '1995-05-31',
            ],
            'olsns.co.uk' => [
                'date' => '21-Feb-2001',
                'format' => 'dmy',
                'expected' => '2001-02-21',
            ],
            'dominis.cat' => [
                'date' => '2017-07-29T11:00:47.438Z',
                'format' => 'mdy',
                'expected' => '2017-07-29',
            ],
            'google.ws' => [
                'date' => '2021-03-03T00:00:00-0800',
                'format' => 'mdy',
                'expected' => '2021-03-03',
            ],
        ];
    }

    private function makeAbstractHandler(): AbstractHandler
    {
        return new class(new WhoisClient()) extends AbstractHandler {
            public function parse(array $rawData, string $query): Data
            {
                throw new \LogicException('Mock');
            }
        };
    }

    #[DataProvider('dateProvider')]
    public function testGetDate(string $date, string $format, string $expected): void
    {
        $obj = $this->makeAbstractHandler();
        $reflectionObj = new \ReflectionObject($obj);
        $reflectionMethod = $reflectionObj->getMethod('getDate');
        $actual = $reflectionMethod->invoke($obj, $date, $format);

        self::assertEquals($expected, $actual);
    }

    #[Group('CVE-2015-5243')]
    public function testGenericParserABlocks(): void
    {
        $translate = [
            'Registrant Name' => 'owner.name',
        ];
        $disclaimer = [];

        $obj = $this->makeAbstractHandler();
        $reflectionObj = new \ReflectionObject($obj);
        $reflectionMethod = $reflectionObj->getMethod('generic_parser_a_blocks');
        $actual = $reflectionMethod->invoke($obj, static::RAW_DATA, $translate, $disclaimer);

        self::assertEquals('${k}', $actual['main']['owner']['name']);
    }

    #[Group('CVE-2015-5243')]
    public function testGenericParserB(): void
    {
        $obj = $this->makeAbstractHandler();
        $reflectionObj = new \ReflectionObject($obj);
        $reflectionMethod = $reflectionObj->getMethod('generic_parser_b');
        $actual = $reflectionMethod->invoke($obj, static::RAW_DATA);

        self::assertEquals('${k}', $actual['owner']['name']);
    }

    #[Group('CVE-2015-5243')]
    public function testGetBlocksOne(): void
    {
        $items = [
            'domain.name' => 'Domain:',
        ];

        $obj = $this->makeAbstractHandler();
        $reflectionObj = new \ReflectionObject($obj);
        $reflectionMethod = $reflectionObj->getMethod('getBlocks');
        $actual = $reflectionMethod->invoke($obj, static::RAW_DATA, $items);

        self::assertEquals('${field}', $actual['domain']['name']);
    }

    #[Group('CVE-2015-5243')]
    public function testGetBlocksTwo(): void
    {
        $items = [
            'agent' => 'Registrar:',
        ];

        $obj = $this->makeAbstractHandler();
        $reflectionObj = new \ReflectionObject($obj);
        $reflectionMethod = $reflectionObj->getMethod('getBlocks');
        $actual = $reflectionMethod->invoke($obj, static::RAW_DATA, $items);

        self::assertEquals("Name:\t \${field}", $actual['agent'][0]);
    }

    #[Group('CVE-2015-5243')]
    public function testGetContact(): void
    {
        $obj = $this->makeAbstractHandler();
        $reflectionObj = new \ReflectionObject($obj);
        $reflectionMethod = $reflectionObj->getMethod('getContact');
        $actual = $reflectionMethod->invoke($obj, static::RAW_DATA);

        self::assertEquals('${field}', $actual['fax']);
    }
}
