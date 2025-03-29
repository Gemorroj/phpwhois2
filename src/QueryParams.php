<?php

namespace PHPWhois2;

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

    public function clear(): void
    {
        $this->server = null;
        $this->query = null;
        $this->args = null;
        $this->handler = null;
        $this->hostIp = null;
        $this->hostName = null;
        $this->serverPort = null;
        $this->tld = null;
        $this->status = null;
        $this->errstr = [];
    }
}
