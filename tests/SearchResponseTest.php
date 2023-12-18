<?php

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use YandexSearchAPI\Correction;
use YandexSearchAPI\Pagination;
use YandexSearchAPI\SearchResponse;
use YandexSearchAPI\SearchRequest;
use YandexSearchAPI\Result;

class SearchResponseTest extends TestCase
{
    private SearchRequest|MockObject $request;

    private SearchResponse $searchResponse;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(SearchRequest::class);
        $this->searchResponse = new SearchResponse($this->request);

        $this->searchResponse->setRequestID('request_number_987654321');
        $this->searchResponse->appendResult('Title 1', 'URL 1', 'Snippet 1');
        $this->searchResponse->appendResult('Title 2', 'URL 2', 'Snippet 2');
    }

    public function testGettingResults(): void
    {
        $results = $this->searchResponse->getResults();

        $this->assertSame($this->request, $this->searchResponse->getRequest());

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

        $this->assertEquals('request_number_987654321', $this->searchResponse->getRequestID());
        $this->assertFalse($this->searchResponse->isError());
        $this->assertNull($this->searchResponse->getErrorCode());
        $this->assertNull($this->searchResponse->getCorrection());
    }

    public function testErrorsHandling(): void
    {
        $this->searchResponse->setErrorText('Error text');
        $this->searchResponse->setErrorCode(123);

        $this->assertTrue($this->searchResponse->isError());
        $this->assertEquals(123, $this->searchResponse->getErrorCode());
        $this->assertEquals('Error text', $this->searchResponse->getErrorText());
    }

    public function testCorrection(): void
    {
        $correctionMock = $this->createMock(Correction::class);

        $this->searchResponse->setCorrection($correctionMock);

        $this->assertSame($correctionMock, $this->searchResponse->getCorrection());
    }

    public function testPagination(): void
    {
        $paginationMock = $this->createMock(Pagination::class);

        $this->searchResponse->setPagination($paginationMock);

        $this->assertSame($paginationMock, $this->searchResponse->getPagination());
    }
}
