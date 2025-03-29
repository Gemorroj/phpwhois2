<?php

namespace PHPWhois2\Handlers\Gtld;

use PHPWhois2\Handlers\AbstractHandler;

class GenericbHandler extends AbstractHandler
{
    public function parse(array $data_str, string $query): array
    {
        return static::generic_parser_b($data_str);
    }
}
