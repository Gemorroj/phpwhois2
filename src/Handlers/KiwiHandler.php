<?php

namespace PHPWhois2\Handlers;

class KiwiHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        $data_str['regrinfo'] = static::generic_parser_b($data_str['rawdata']);
        $data_str['regyinfo'] = $this->parseRegistryInfo($data_str['rawdata']);

        return $data_str;
    }
}
