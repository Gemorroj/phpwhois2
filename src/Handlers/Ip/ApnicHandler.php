<?php

namespace PHPWhois2\Handlers\Ip;

use PHPWhois2\Handlers\AbstractHandler;

class ApnicHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $translate = [
            'fax-no' => 'fax',
            'e-mail' => 'email',
            'nic-hdl' => 'handle',
            'person' => 'name',
            'netname' => 'name',
            'descr' => 'desc',
            'aut-num' => 'handle',
            'country' => 'country',
        ];

        $contacts = [
            'admin-c' => 'admin',
            'tech-c' => 'tech',
        ];

        $disclaimer = [];
        $blocks = static::generic_parser_a_blocks($data_str, $translate, $disclaimer);

        $r = [];

        if (isset($disclaimer) && \is_array($disclaimer)) {
            $r['disclaimer'] = $disclaimer;
        }

        if (empty($blocks) || !\is_array($blocks['main'])) {
            $r['registered'] = 'no';
        } else {
            if (isset($blocks[$query])) {
                $rb = $blocks[$query];
            } else {
                $rb = $blocks['main'];
            }

            $r['registered'] = 'yes';

            foreach ($contacts as $key => $val) {
                if (isset($rb[$key])) {
                    if (\is_array($rb[$key])) {
                        $blk = $rb[$key][\count($rb[$key]) - 1];
                    } else {
                        $blk = $rb[$key];
                    }

                    if (isset($blocks[$blk])) {
                        $r[$val] = $blocks[$blk];
                    }
                    unset($rb[$key]);
                }
            }

            $r['network'] = $rb;
            static::formatDates($r, 'Ymd');

            if (isset($r['network']['desc'])) {
                if (\is_array($r['network']['desc'])) {
                    $r['owner']['organization'] = \array_shift($r['network']['desc']);
                    $r['owner']['address'] = $r['network']['desc'];
                } else {
                    $r['owner']['organization'] = $r['network']['desc'];
                }

                unset($r['network']['desc']);
            }

            if (isset($r['network']['address'])) {
                if (isset($r['owner']['address'])) {
                    $r['owner']['address'][] = $r['network']['address'];
                } else {
                    $r['owner']['address'] = $r['network']['address'];
                }

                unset($r['network']['address']);
            }
        }

        $r = ['regrinfo' => $r];
        $r['regyinfo']['type'] = 'ip';
        $r['regyinfo']['registrar'] = 'Asia Pacific Network Information Centre';

        return $r;
    }
}
