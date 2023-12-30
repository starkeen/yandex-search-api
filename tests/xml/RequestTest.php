<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests\xml;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\xml\Request;

class RequestTest extends TestCase
{
    public function testFetchingAttributes(): void
    {
        $xml = new Request('<request><query>example</query></request>');

        $this->assertEquals('example', $xml->getQuery());
    }
}
