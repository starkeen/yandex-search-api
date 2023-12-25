<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests\xml;

use YandexSearchAPI\Tests\AbstractTestCase;
use YandexSearchAPI\xml\ResponseRoot;

class FullXMLResponseTest extends AbstractTestCase
{
    public function testFullResponse(): void
    {
        $xmlString = $this->getFullResponseXML();

        $xml = new ResponseRoot($xmlString);

        $this->assertEquals('yandex', $xml->getRequest()->getQuery());

        $this->assertEquals(
            '1348828873568466-1289158387737177180255457-3-011-XML',
            $xml->getResponse()->getRequestID()
        );
        $this->assertEquals(null, $xml->getResponse()->getError());
    }
}
