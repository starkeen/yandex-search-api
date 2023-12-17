<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use YandexSearchAPI\SearchRequest;

abstract class AbstractTestCase extends TestCase
{
    protected function getHttpClientMock(): Client|MockObject
    {
        return $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['post'])
            ->getMock();
    }

    protected function getHttpResponseMock(): Response|MockObject
    {
        return $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBody', 'getStatusCode'])
            ->getMock();
    }

    protected function getHttpResponseBodyMock(): Stream|MockObject
    {
        return $this->getMockBuilder(Stream::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getContents'])
            ->getMock();
    }

    protected function getHttpResponseClientExceptionMock(): ClientException|MockObject
    {
        return $this->getMockBuilder(ClientException::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getLoggerMock(): LoggerInterface|MockObject
    {
        return $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getRequestMock(): SearchRequest|MockObject
    {
        return $this->getMockBuilder(SearchRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
