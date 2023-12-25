<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests\xml;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\xml\Response;

class ResponseTest extends TestCase
{
    public function testFetchingRequestID(): void
    {
        $xml = new Response('<response><reqid>bar</reqid></response>');

        $this->assertEquals('bar', $xml->getRequestID());
    }

    public function testWithoutMispellings(): void
    {
        $xml = new Response('<response><reqid>bar</reqid></response>');

        $this->assertNull($xml->getMisspelling());
    }
}
