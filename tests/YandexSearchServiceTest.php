<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests;

use YandexSearchAPI\ConfigurationException;
use YandexSearchAPI\SearchException;
use YandexSearchAPI\YandexSearchService;

class YandexSearchServiceTest extends AbstractTestCase
{
    public function testCheckingConfiguration(): void
    {
        $service = new YandexSearchService(
            $this->getHttpClientMock(),
            $this->getHttpFactory(),
            $this->getLoggerMock()
        );

        $this->expectException(ConfigurationException::class);
        $service->search($this->getRequestMock());
    }

    public function testCredentialsCanBeProvidedViaConstructor(): void
    {
        $httpClient = $this->getHttpClientMock();
        $httpClient->method('sendRequest')
            ->willReturn($this->getHttpResponseMock($this->getFullResponseXML()));

        $service = new YandexSearchService(
            $httpClient,
            $this->getHttpFactory(),
            $this->getLoggerMock(),
            '123',
            '456'
        );

        $result = $service->search($this->getRequestMock());

        $this->assertEquals('1348828873568466-1289158387737177180255457-3-011-XML', $result->getRequestID());
    }

    public function testWrongAPIResponse(): void
    {
        $httpClient = $this->getHttpClientMock();
        $httpClient->method('sendRequest')
            ->willThrowException($this->getHttpTransportException());

        $service = new YandexSearchService(
            $httpClient,
            $this->getHttpFactory(),
            $this->getLoggerMock()
        );
        $service->setApiId('123');
        $service->setApiKey('456');

        $this->expectException(SearchException::class);
        $service->search($this->getRequestMock());
    }

    public function testWrongXMLResponse(): void
    {
        $httpClient = $this->getHttpClientMock();
        $httpClient->method('sendRequest')
            ->willReturn($this->getHttpResponseMock('wrong xml'));

        $service = new YandexSearchService(
            $httpClient,
            $this->getHttpFactory(),
            $this->getLoggerMock()
        );
        $service->setApiId('123');
        $service->setApiKey('456');

        $this->expectException(SearchException::class);
        $service->search($this->getRequestMock());
    }

    public function testResponseParsing(): void
    {
        $httpClient = $this->getHttpClientMock();
        $httpClient->method('sendRequest')
            ->willReturn($this->getHttpResponseMock($this->getFullResponseXML()));

        $service = new YandexSearchService(
            $httpClient,
            $this->getHttpFactory(),
            $this->getLoggerMock()
        );
        $service->setApiId('123');
        $service->setApiKey('456');

        $request = $this->getRequestMock();
        $result = $service->search($request);

        $this->assertSame($request, $result->getRequest());
        $this->assertEquals('1348828873568466-1289158387737177180255457-3-011-XML', $result->getRequestID());

        $this->assertEquals('нашёл 193 млн ответов', $result->getPagination()->getTotalHuman());
        $this->assertEquals(10, $result->getPagination()->getPageSize());
        $this->assertEquals(45094, $result->getPagination()->getTotal());
        $this->assertEquals(0, $result->getPagination()->getCurrentPage());
        $this->assertEquals(4510, $result->getPagination()->getPagesCount());

        $this->assertEquals('yandexx', $result->getCorrection()->getSourceText());
        $this->assertEquals('yandex', $result->getCorrection()->getResultText());

        $this->assertCount(1, $result->getResults());
        $this->assertEquals('https://www.yandex.ru/', $result->getResults()[0]->getURL());
        $this->assertEquals('www.yandex.ru', $result->getResults()[0]->getDomain());
        $this->assertEquals('"Яндекс" - поисковая система и интернет-портал', $result->getResults()[0]->getTitle());
        $this->assertEquals('Яндекс — поисковая машина, способная...', $result->getResults()[0]->getSnippet());
    }

    public function testResponseWithMultipleGroupsAndPassages(): void
    {
        $httpClient = $this->getHttpClientMock();
        $httpClient->method('sendRequest')
            ->willReturn($this->getHttpResponseMock($this->getMultiGroupResponseXML()));

        $service = new YandexSearchService(
            $httpClient,
            $this->getHttpFactory(),
            $this->getLoggerMock()
        );
        $service->setApiId('123');
        $service->setApiKey('456');

        $result = $service->search($this->getRequestMock());
        $results = $result->getResults();

        $this->assertCount(2, $results);

        $this->assertEquals('https://example.com/first', $results[0]->getURL());
        $this->assertEquals('First doc', $results[0]->getTitle());
        $this->assertEquals('first passage second passage', $results[0]->getSnippet());

        $this->assertEquals('https://example.org/second', $results[1]->getURL());
        $this->assertEquals('Second doc', $results[1]->getTitle());
        $this->assertEquals('only passage', $results[1]->getSnippet());

        $this->assertEquals(2, $result->getPagination()->getTotal());
        $this->assertNull($result->getCorrection());
    }

    public function testResponseWithoutResultsYieldsEmptyResults(): void
    {
        $httpClient = $this->getHttpClientMock();
        $httpClient->method('sendRequest')
            ->willReturn($this->getHttpResponseMock($this->getNoResultsResponseXML()));

        $service = new YandexSearchService(
            $httpClient,
            $this->getHttpFactory(),
            $this->getLoggerMock()
        );
        $service->setApiId('123');
        $service->setApiKey('456');

        $result = $service->search($this->getRequestMock());

        $this->assertSame([], $result->getResults());
        $this->assertNull($result->getPagination());
    }

    public function testResponseWithError(): void
    {
        $httpClient = $this->getHttpClientMock();
        $httpClient->method('sendRequest')
            ->willReturn($this->getHttpResponseMock($this->getErrorResponseXML()));

        $service = new YandexSearchService(
            $httpClient,
            $this->getHttpFactory(),
            $this->getLoggerMock()
        );
        $service->setApiId('123');
        $service->setApiKey('456');

        $this->expectException(SearchException::class);
        $this->expectExceptionCode(15);
        $this->expectExceptionMessage('Искомая комбинация слов нигде не встречается');
        $service->search($this->getRequestMock());
    }
}
