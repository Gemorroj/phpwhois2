<?php

namespace PHPWhois2\Handlers;

class UkHandler extends AbstractHandler
{
    public const ITEMS = [
        'owner.organization' => 'Registrant:',
        'owner.address' => "Registrant's address:",
        'owner.type' => 'Registrant type:',
        'domain.created' => 'Registered on:',
        'domain.changed' => 'Last updated:',
        'domain.expires' => 'Expiry date:',
        'domain.nserver' => 'Name servers:',
        'domain.sponsor' => 'Registrar:',
        'domain.status' => 'Registration status:',
        'domain.dnssec' => 'DNSSEC:',
        '' => 'WHOIS lookup made at',
        'disclaimer' => '--',
    ];

    public function parse(array $data_str, string $query): array
    {
        $rawData = $this->removeBlankLines($data_str['rawdata']);

        $r = [
            'regrinfo' => static::getBlocks($rawData, static::ITEMS),
            'regyinfo' => $this->parseRegistryInfo($data_str['rawdata']) ?? [
                'referrer' => 'https://www.nominet.org.uk',
                'registrar' => 'Nominet UK',
            ],
            'rawdata' => $data_str['rawdata'] ?? null,
        ];

        if (isset($r['regrinfo']['owner'])) {
            $r['regrinfo']['owner']['organization'] = $r['regrinfo']['owner']['organization'][0];
            $r['regrinfo']['domain']['sponsor'] = $r['regrinfo']['domain']['sponsor'][0];
            $r['regrinfo']['registered'] = 'yes';
        } elseif (\strpos($query, '.co.uk') && isset($r['regrinfo']['domain']['status'][0])) {
            if ('Registered until expiry date.' === $r['regrinfo']['domain']['status'][0]) {
                $r['regrinfo']['registered'] = 'yes';
            }
        } elseif (\strpos($data_str['rawdata'][1], 'Error for ')) {
            $r['regrinfo']['registered'] = 'yes';
            $r['regrinfo']['domain']['status'] = 'invalid';
        } else {
            $r['regrinfo']['registered'] = 'no';
        }

        return static::formatDates($r, 'dmy');
    }
}
