<?php

namespace PHPWhois2\Tests\Handlers;

use PHPUnit\Framework\TestCase;
use PHPWhois2\Handlers\AbstractHandler as CommonAbstractHandler;

abstract class AbstractHandler extends TestCase
{
    protected CommonAbstractHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\defined('DEBUG_MODE')) {
            \define('DEBUG_MODE', true);
        }
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function loadFixture(string $which): array
    {
        $fixture = \sprintf(
            '%s/fixtures/%s.txt',
            \dirname(__DIR__),
            $which
        );
        if (\file_exists($fixture)) {
            $raw = \file_get_contents($fixture);

            // Testing on Windows introduces carriage returns
            $raw = \str_replace("\r", '', $raw);

            // Split the lines the same way as WhoisClient::getRawData()
            return \explode("\n", $raw);
        }

        throw new \InvalidArgumentException("Cannot find fixture `{$fixture}`");
    }
}
