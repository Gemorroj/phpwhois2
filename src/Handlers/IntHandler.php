<?php

namespace PHPWhois2\Handlers;

use PHPWhois2\Handlers\Gtld\IanaHandler;

class IntHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $iana = new IanaHandler($this->whoisClient, $this->deepWhois);
        $r = [];
        $r['regrinfo'] = $iana->parse($data_str['rawdata'], $query);
        $r['regyinfo']['referrer'] = 'http://www.iana.org/int-dom/int.htm';
        $r['regyinfo']['registrar'] = 'Internet Assigned Numbers Authority';

        return $r;
    }
}
