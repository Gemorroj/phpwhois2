<?php

namespace PHPWhois2\Handlers\Ip;

use PHPWhois2\Handlers\AbstractHandler;

class AfrinicHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
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

        $r = static::generic_parser_a($data_str, $translate, $contacts, 'network', 'Ymd');

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

        $r = ['regrinfo' => $r];
        $r['regyinfo']['type'] = 'ip';
        $r['regyinfo']['registrar'] = 'African Network Information Center';

        return $r;
    }
}
