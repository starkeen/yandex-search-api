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
        $httpClient = $this->getHttpClientMock();
        $logger = $this->getLoggerMock();

        $service = new YandexSearchService($httpClient, $logger);

        $request = $this->getRequestMock();

        $this->expectException(ConfigurationException::class);
        $service->search($request);
    }

    public function testWrongAPIResponse(): void
    {
        $httpClient = $this->getHttpClientMock();
        $httpClient->method('post')->willThrowException($this->getHttpResponseClientExceptionMock());

        $logger = $this->getLoggerMock();

        $service = new YandexSearchService($httpClient, $logger);
        $service->setApiId('123');
        $service->setApiKey('456');

        $request = $this->getRequestMock();

        $this->expectException(SearchException::class);
        $service->search($request);
    }

    public function testWrongXMLResponse(): void
    {
        $responseBody = $this->getHttpResponseBodyMock();
        $responseBody->method('getContents')->willReturn('wrong xml');

        $httpResponse = $this->getHttpResponseMock();
        $httpResponse->method('getStatusCode')->willReturn(200);
        $httpResponse->method('getBody')->willReturn($responseBody);

        $httpClient = $this->getHttpClientMock();
        $httpClient->method('post')->willReturn($httpResponse);

        $logger = $this->getLoggerMock();

        $service = new YandexSearchService($httpClient, $logger);
        $service->setApiId('123');
        $service->setApiKey('456');

        $request = $this->getRequestMock();

        $this->expectException(SearchException::class);
        $service->search($request);
    }

    public function testResponseParsing(): void
    {
        $responseBody = $this->getHttpResponseBodyMock();
        $responseBody->method('getContents')->willReturn($this->getFullResponseXML());

        $httpResponse = $this->getHttpResponseMock();
        $httpResponse->method('getStatusCode')->willReturn(200);
        $httpResponse->method('getBody')->willReturn($responseBody);

        $httpClient = $this->getHttpClientMock();
        $httpClient->method('post')->willReturn($httpResponse);

        $logger = $this->getLoggerMock();

        $service = new YandexSearchService($httpClient, $logger);
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

    public function testResponseWithError(): void
    {
        $responseBody = $this->getHttpResponseBodyMock();
        $responseBody->method('getContents')->willReturn($this->getErrorResponseXML());

        $httpResponse = $this->getHttpResponseMock();
        $httpResponse->method('getStatusCode')->willReturn(200);
        $httpResponse->method('getBody')->willReturn($responseBody);

        $httpClient = $this->getHttpClientMock();
        $httpClient->method('post')->willReturn($httpResponse);

        $logger = $this->getLoggerMock();

        $service = new YandexSearchService($httpClient, $logger);
        $service->setApiId('123');
        $service->setApiKey('456');

        $request = $this->getRequestMock();

        $this->expectException(SearchException::class);
        $this->expectExceptionCode(15);
        $this->expectExceptionMessage('Искомая комбинация слов нигде не встречается');
        $service->search($request);
    }
}
