<?php

namespace PHPWhois2\Handler\Ip;

use PHPWhois2\Data;
use PHPWhois2\Handler\AbstractHandler;

class ApnicHandler extends AbstractHandler
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
            'descr' => 'desc',
            'aut-num' => 'handle',
            'country' => 'country',
        ];

        $contacts = [
            'admin-c' => 'admin',
            'tech-c' => 'tech',
        ];

        $disclaimer = [];
        $blocks = $this->generic_parser_a_blocks($rawData, $translate, $disclaimer);

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
            $this->formatDates($r, 'Ymd');

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

        $data = new Data();
        $data->regrinfo = $r;
        $data->regyinfo = [
            'type' => 'ip',
            'registrar' => 'Asia Pacific Network Information Centre',
        ];
        $data->rawData = $rawData;

        return $data;
    }
}
