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
}
