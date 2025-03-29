<?php

namespace phpWhois;

final class QueryParams
{
    public ?string $server = null;
    public ?string $query = null;
    public ?string $args = null;
    public ?string $handler = null;
    public ?string $hostIp = null;
    public ?string $hostName = null;
    public ?int $serverPort = null;
    public ?string $tld = null;
    public ?string $status = null; // error | ready | ok
    /** @var string[] */
    public array $errstr = [];
}
