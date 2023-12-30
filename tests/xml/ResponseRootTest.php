<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests\xml;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\xml\ResponseRoot;

class ResponseRootTest extends TestCase
{
    public function testFetchingRequest(): void
    {
        $xml = new ResponseRoot('<root><request><foo>bar</foo></request></root>');

        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<request><foo>bar</foo></request>\n",
            $xml->getRequest()->asXML()
        );
    }

    public function testFetchingResponse(): void
    {
        $xml = new ResponseRoot('<root><response><baz>bar</baz></response></root>');

        $this->assertEquals(
            "<?xml version=\"1.0\"?>\n<response><baz>bar</baz></response>\n",
            $xml->getResponse()->asXML()
        );
    }
}
