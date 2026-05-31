<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use YandexSearchAPI\SearchRequest;

abstract class AbstractTestCase extends TestCase
{
    protected function getHttpClientMock(): ClientInterface&MockObject
    {
        return $this->createMock(ClientInterface::class);
    }

    protected function getHttpFactory(): RequestFactoryInterface&StreamFactoryInterface
    {
        return new Psr17Factory();
    }

    protected function getHttpResponseMock(string $body): Response
    {
        return new Response(200, [], $body);
    }

    protected function getHttpTransportException(): \Psr\Http\Client\ClientExceptionInterface
    {
        return new class extends \RuntimeException implements \Psr\Http\Client\ClientExceptionInterface {};
    }

    protected function getLoggerMock(): LoggerInterface&MockObject
    {
        return $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getRequestMock(): SearchRequest&MockObject
    {
        return $this->getMockBuilder(SearchRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getFullResponseXML(): string
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

    protected function getMultiGroupResponseXML(): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<yandexsearch version="1.0">
<request>
   <query>yandex</query>
   <page>0</page>
</request>
<response date="20120928T103130">
   <reqid>req-multi</reqid>
   <results>
      <grouping attr="d" mode="flat" groups-on-page="10" docs-in-group="1">
         <found priority="all">2</found>
         <found-docs-human>нашёл 2 ответа</found-docs-human>
         <page first="1" last="10">0</page>
         <group>
            <doccount>1</doccount>
            <doc id="d1">
               <url>https://example.com/first</url>
               <title>First <hlword>doc</hlword></title>
               <passages>
                  <passage>first <hlword>passage</hlword></passage>
                  <passage>second passage</passage>
               </passages>
            </doc>
         </group>
         <group>
            <doccount>1</doccount>
            <doc id="d2">
               <url>https://example.org/second</url>
               <title>Second doc</title>
               <passages>
                  <passage>only passage</passage>
               </passages>
            </doc>
         </group>
      </grouping>
   </results>
</response>
</yandexsearch>
XML;
    }

    protected function getNoResultsResponseXML(): string
    {
        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<yandexsearch version="1.0">
<request>
   <query>yandex</query>
   <page>0</page>
</request>
<response date="20120928T103130">
   <reqid>1348828873568466-1289158387737177180255457-3-011-XML</reqid>
</response>
</yandexsearch>
XML;
    }

    protected function getErrorResponseXML(): string
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
