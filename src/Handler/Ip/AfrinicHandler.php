<?php

namespace PHPWhois2\Handler\Ip;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class AfrinicHandler extends AbstractHandler
{
    public function parse(array $rawData, string $query): Data
    {
        $this->whoisClient->queryParams->handlerClass = self::class;
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl' => 'handle',
            'person' => 'name',
            'netname' => 'name',
            'organisation' => 'handle',
            'org-name' => 'organization',
            'org-type' => 'type',
        ];

        $contacts = [
            'admin-c' => 'admin',
            'tech-c' => 'tech',
            'org' => 'owner',
        ];

        $r = $this->generic_parser_a($rawData, $translate, $contacts, 'network', 'Ymd');

        if (isset($r['network']['descr'])) {
            $r['owner']['organization'] = $r['network']['descr'];
            unset($r['network']['descr']);
        }

        if (isset($r['owner']['remarks']) && \is_array($r['owner']['remarks'])) {
            foreach ($r['owner']['remarks'] as $val) {
                $pos = \strpos($val, 'rwhois://');

                if (false !== $pos) {
                    $r['rwhois'] = \strtok(\substr($val, $pos), ' ');
                }
            }
        }

        $data = new Data();
        $data->regrinfo = $r;
        $data->regyinfo = [
            'type' => 'ip',
            'registrar' => 'African Network Information Center',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
