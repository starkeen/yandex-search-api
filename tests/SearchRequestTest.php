<?php

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\ConfigurationException;
use YandexSearchAPI\constant\Filter;
use YandexSearchAPI\constant\Language;
use YandexSearchAPI\constant\Sort;
use YandexSearchAPI\SearchRequest;

class SearchRequestTest extends TestCase
{
    private ?SearchRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = new SearchRequest('test');
    }

    public function testDefaultXML(): void
    {
        $expectedXMLString = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<request><query>test</query><page>0</page><maxpassages>4</maxpassages><groupings><groupby mode="flat" groups-on-page="10" docs-in-group="1"/></groupings></request>

XML;

        $this->assertEquals($expectedXMLString, $this->request->getXML());
    }

    public function testGetXMLValuesUpdated(): void
    {
        $this->request->setNumResults(20);
        $this->request->setPage(1);
        $this->request->setMaxPassages(5);

        $expectedXMLString = <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<request><query>test</query><page>1</page><maxpassages>5</maxpassages><groupings><groupby mode="flat" groups-on-page="20" docs-in-group="1"/></groupings></request>

XML;

        $this->assertEquals($expectedXMLString, $this->request->getXML());
    }

    public function testCorrectLanguage(): void
    {
        $this->request->setLanguage(Language::TURKISH);

        $this->assertEquals(Language::TURKISH, $this->request->getLanguage());
    }

    public function testIncorrectLanguage(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->request->setLanguage('wrong');
    }

    public function testCorrectSort(): void
    {
        $this->request->setSort(Sort::RELEVANCE);

        $this->assertEquals(Sort::RELEVANCE, $this->request->getSort());
    }

    public function testIncorrectSort(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->request->setSort('wrong');
    }

    public function testCorrectFilter(): void
    {
        $this->request->setFilter(Filter::STRICT);

        $this->assertEquals(Filter::STRICT, $this->request->getFilter());
    }

    public function testIncorrectFilter(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->request->setFilter('wrong');
    }
}
