<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class FastdomainHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $items = [
            'owner' => 'Registrant Info:',
            'admin' => 'Administrative Info:',
            'tech' => 'Technical Info:',
            'domain.name' => 'Domain Name:',
            'domain.sponsor' => 'Provider Name....:',
            'domain.referrer' => 'Provider Homepage:',
            'domain.nserver' => 'Domain servers in listed order:',
            'domain.created' => 'Created on..............:',
            'domain.expires' => 'Expires on..............:',
            'domain.changed' => 'Last modified on........:',
            'domain.status' => 'Status:',
        ];

        foreach ($data_str as $key => $val) {
            $faststr = \strpos($val, ' (FAST-');
            if ($faststr) {
                $data_str[$key] = \substr($val, 0, $faststr);
            }
        }

        $r = static::easyParser($data_str, $items, 'dmy', [], false, true);

        if (isset($r['domain']['sponsor']) && \is_array($r['domain']['sponsor'])) {
            $r['domain']['sponsor'] = $r['domain']['sponsor'][0];
        }

        if (isset($r['domain']['nserver'])) {
            foreach ($r['domain']['nserver'] as $key => $val) {
                if ('=-=-=-=' === $val) {
                    unset($r['domain']['nserver'][$key]);
                }
            }
        }

        return $r;
    }
}
