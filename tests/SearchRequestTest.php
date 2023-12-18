<?php

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\SearchRequest;

class SearchRequestTest extends TestCase
{
    private ?SearchRequest $search;

    protected function setUp(): void
    {
        parent::setUp();

        $this->search = new SearchRequest('test');
    }

    public function testDefaultXML(): void
    {
        $expectedXMLString = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<request><query>test</query><page>0</page><maxpassages>4</maxpassages><groupings><groupby mode="flat" groups-on-page="10" docs-in-group="1"/></groupings></request>

XML;

        $this->assertEquals($expectedXMLString, $this->search->getXML());
    }

    public function testGetXMLValuesUpdated(): void
    {
        $this->search->setNumResults(20);
        $this->search->setPage(1);
        $this->search->setMaxPassages(5);

        $expectedXMLString = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<request><query>test</query><page>1</page><maxpassages>5</maxpassages><groupings><groupby mode="flat" groups-on-page="20" docs-in-group="1"/></groupings></request>

XML;

        $this->assertEquals($expectedXMLString, $this->search->getXML());
    }
}
