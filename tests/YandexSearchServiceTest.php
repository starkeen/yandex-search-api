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

    private function getFullResponseXML(): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<yandexsearch version="1.0">
<request>
   <query>yandex</query>
   <page>0</page>
   <sortby order="descending" priority="no">rlv</sortby>
   <maxpassages>2</maxpassages>
   <groupings>
      <groupby  attr="d" mode="deep" groups-on-page="10" docs-in-group="3" curcateg="-1" />
   </groupings>
</request>
<response date="20120928T103130">
   <reqid>1348828873568466-1289158387737177180255457-3-011-XML</reqid>
   <found priority="phrase">206775197</found>
   <found priority="strict">206775197</found>
   <found priority="all">206775197</found>
   <found-human>Нашлось 207 млн ответов</found-human>
   <misspell>
      <rule>Misspell</rule>
      <source-text>yande<hlword>xx</hlword></source-text>
      <text>yandex</text>
   </misspell>
   <reask>
      <rule>Misspell</rule>
      <source-text><hlword>yn</hlword>dex</source-text>
      <text-to-show>yandex</text-to-show>
      <text>yandex</text>
   </reask>
   <results>
      <grouping attr="d" mode="deep" groups-on-page="10" docs-in-group="3" curcateg="-1">
         <found priority="phrase">45094</found>
         <found priority="strict">45094</found>
         <found priority="all">45094</found>
         <found-docs priority="phrase">192685602</found-docs>
         <found-docs priority="strict">192685602</found-docs>
         <found-docs priority="all">192685602</found-docs>
         <found-docs-human>нашёл 193 млн ответов</found-docs-human>
         <page first="1" last="10">0</page>
         <group>
            <categ attr="d" name="UngroupVital223.ru" />
            <doccount>34</doccount>
            <relevance priority="all" />
            <doc id="ZD831E1113BCFDD95">
               <relevance priority="phrase" />
               <url>https://www.yandex.ru/</url>
               <domain>www.yandex.ru</domain>
               <title>&quot;<hlword>Яндекс</hlword>&quot; - поисковая система и интернет-портал</title>
               <headline>Поиск по всему интернету с учетом региона пользователя.</headline>
               <modtime>20060814T040000</modtime>
               <size>26938</size>
               <charset>utf-8</charset>
               <passages>
                  <passage><hlword>Яндекс</hlword> — поисковая машина, способная...</passage>
               </passages>
               <properties>
                   <_PassagesType>0</_PassagesType>
                   <lang>ru</lang>
               </properties>
               <mime-type>text/html</mime-type>
               <saved-copy-url>https://hghltd.yandex.net/yandbtm?text=yandex&amp;url=https%3A%2F%2Fwww.yandex.ru%2F&amp;fmode=inject&amp;mime=html&amp;l10n=ru&amp;sign=e3737561fc3d1105967d1ce619dbd3c7&amp;keyno=0</saved-copy-url>
            </doc>
         </group>
      </grouping>
   </results>
</response>
</yandexsearch>
XML;
    }

    private function getErrorResponseXML(): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<yandexsearch version="1.0">
<request>
   <query>yandex</query>
   <page>0</page>
   <sortby order="descending" priority="no">rlv</sortby>
   <maxpassages>2</maxpassages>
   <groupings>
      <groupby  attr="d" mode="deep" groups-on-page="10" docs-in-group="3" curcateg="-1" />
   </groupings>
</request>
<response date="20120928T103130">
   <error code="15">Искомая комбинация слов нигде не встречается</error>
   <reqid>1348828873568466-1289158387737177180255457-3-011-XML</reqid>
</response>
</yandexsearch>
XML;
    }
}
