<?php

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\SearchResponse;
use YandexSearchAPI\SearchRequest;
use YandexSearchAPI\Result;

class SearchResponseTest extends TestCase
{
    private SearchResponse $searchResponse;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $request = $this->createMock(SearchRequest::class);
        $this->searchResponse = new SearchResponse($request);

        $this->searchResponse->appendResult('Title 1', 'URL 1', 'Snippet 1');
        $this->searchResponse->appendResult('Title 2', 'URL 2', 'Snippet 2');
    }

    public function testGetResults(): void
    {
        $results = $this->searchResponse->getResults();

        $this->assertIsArray($results);
        $this->assertCount(2, $results);
        $this->assertInstanceOf(Result::class, $results[0]);
        $this->assertEquals('Title 1', $results[0]->getTitle());
        $this->assertEquals('URL 1', $results[0]->getURL());
        $this->assertEquals('Snippet 1', $results[0]->getSnippet());

        $this->assertInstanceOf(Result::class, $results[1]);
        $this->assertEquals('Title 2', $results[1]->getTitle());
        $this->assertEquals('URL 2', $results[1]->getURL());
        $this->assertEquals('Snippet 2', $results[1]->getSnippet());
        
    }
}
