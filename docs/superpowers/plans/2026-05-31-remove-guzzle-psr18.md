# Remove Guzzle — Migrate to PSR-18/PSR-17 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace `guzzlehttp/guzzle` with the standard PSR-18 HTTP client and PSR-17 factory interfaces, making the library implementation-agnostic.

**Architecture:** `YandexSearchService` accepts a `Psr\Http\Client\ClientInterface` and a combined `RequestFactoryInterface&StreamFactoryInterface` factory; it builds PSR-7 requests internally and calls `sendRequest()`. Tests use a real `Nyholm\Psr7\Factory\Psr17Factory` for the factory and a real `Nyholm\Psr7\Response` for response objects — no Guzzle classes anywhere.

**Tech Stack:** `psr/http-client ^1.0`, `psr/http-factory ^1.0`, `nyholm/psr7 ^1.8` (dev only), PHP 8.1+, PHPUnit 10/11.

---

### Task 1: Update `composer.json`

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Edit `require` and `require-dev`**

Replace the `require` and `require-dev` blocks so they look like this (keep everything else intact):

```json
"require": {
    "php": "^8.1",
    "ext-libxml": "*",
    "ext-simplexml": "*",
    "psr/http-client": "^1.0",
    "psr/http-factory": "^1.0",
    "psr/log": "^3.0"
},
```

```json
"require-dev": {
    "nyholm/psr7": "^1.8",
    "phpmd/phpmd": "^2.15",
    "phpstan/phpstan": "^2.0",
    "phpunit/phpunit": "^10.5 || ^11.5",
    "squizlabs/php_codesniffer": "^3.8"
},
```

- [ ] **Step 2: Install updated dependencies**

```bash
composer update
```

Expected: Guzzle packages are removed, `psr/http-client`, `psr/http-factory`, and `nyholm/psr7` appear in `vendor/`.

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore: replace guzzlehttp/guzzle with psr/http-client + psr/http-factory"
```

---

### Task 2: Migrate `YandexSearchService`

**Files:**
- Modify: `src/YandexSearchService.php`

- [ ] **Step 1: Replace the file content**

Replace the entire file with:

```php
<?php

/** @noinspection MethodShouldBeFinalInspection */

declare(strict_types=1);

namespace YandexSearchAPI;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use YandexSearchAPI\parser\ResultsParser;
use YandexSearchAPI\xml\ResponseRoot;

class YandexSearchService
{
    private const YANDEX_SEARCH_URL = 'https://yandex.ru/search/xml';

    private ClientInterface $httpClient;
    /** @var RequestFactoryInterface&StreamFactoryInterface */
    private RequestFactoryInterface $factory;
    private LoggerInterface $logger;

    private ?string $apiId;
    private ?string $apiKey;

    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface&StreamFactoryInterface $factory
     * @param LoggerInterface $logger
     * @param string|null $apiId  Folder ID from your Yandex Cloud account
     * @param string|null $apiKey API key from your Yandex Cloud account
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface&StreamFactoryInterface $factory,
        LoggerInterface $logger,
        ?string $apiId = null,
        ?string $apiKey = null
    ) {
        $this->httpClient = $client;
        $this->factory = $factory;
        $this->logger = $logger;
        $this->apiId = $apiId;
        $this->apiKey = $apiKey;
    }

    /**
     * @param SearchRequest $request
     * @return SearchResponse
     */
    public function search(SearchRequest $request): SearchResponse
    {
        if ($this->apiId === null || $this->apiKey === null) {
            throw new ConfigurationException('API ID and API key must be set');
        }

        $uri = self::YANDEX_SEARCH_URL . '?' . http_build_query([
            'folderid' => $this->apiId,
            'l10n'     => $request->getLanguage(),
            'sortby'   => $request->getSort(),
            'filter'   => $request->getFilter(),
        ]);

        $psrRequest = $this->factory->createRequest('POST', $uri)
            ->withHeader('Content-Type', 'application/xml')
            ->withHeader('Authorization', 'Api-Key ' . $this->apiKey)
            ->withBody($this->factory->createStream($request->getXML()));

        try {
            $rawResponse = $this->httpClient->sendRequest($psrRequest);
        } catch (ClientExceptionInterface | Throwable $exception) {
            $this->logger->error(
                'Yandex search API error',
                [
                    'exception' => $exception,
                    'request' => $request,
                ]
            );

            throw new SearchException('Yandex search API error', 0, $exception);
        }

        $previousXmlErrorMode = libxml_use_internal_errors(true);

        try {
            $xml = new ResponseRoot($rawResponse->getBody()->getContents());
        } catch (Exception $exception) {
            $this->logger->error(
                'Yandex search API response parse error',
                [
                    'request' => $request,
                ]
            );

            throw new SearchException('Yandex search API response parse error', 0, $exception);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousXmlErrorMode);
        }

        $xmlResponse = $xml->getResponse();

        return (new ResultsParser())->parse($request, $xmlResponse);
    }

    /**
     * @deprecated since 2.0, pass the Folder ID to the constructor instead.
     *
     * @param string $apiId
     * @return void
     */
    public function setApiId(string $apiId): void
    {
        $this->apiId = $apiId;
    }

    /**
     * @deprecated since 2.0, pass the API key to the constructor instead.
     *
     * @param string $apiKey
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
```

- [ ] **Step 2: Verify static analysis still passes (no Guzzle references)**

```bash
grep -r "GuzzleHttp" src/
```

Expected: no output.

- [ ] **Step 3: Commit**

```bash
git add src/YandexSearchService.php
git commit -m "feat!: replace GuzzleHttp\\ClientInterface with PSR-18/PSR-17"
```

---

### Task 3: Update `AbstractTestCase`

**Files:**
- Modify: `tests/AbstractTestCase.php`

- [ ] **Step 1: Replace the file content**

Replace the entire file with:

```php
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
```

- [ ] **Step 2: Verify no Guzzle imports remain**

```bash
grep -n "GuzzleHttp" tests/AbstractTestCase.php
```

Expected: no output.

- [ ] **Step 3: Commit**

```bash
git add tests/AbstractTestCase.php
git commit -m "test: migrate AbstractTestCase from Guzzle to PSR-18/Nyholm helpers"
```

---

### Task 4: Update `YandexSearchServiceTest`

**Files:**
- Modify: `tests/YandexSearchServiceTest.php`

- [ ] **Step 1: Replace the file content**

Replace the entire file with:

```php
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
```

- [ ] **Step 2: Run the full test suite**

```bash
composer test
```

Expected: all tests pass, zero failures.

- [ ] **Step 3: Verify no Guzzle references anywhere in tests**

```bash
grep -rn "GuzzleHttp" tests/
```

Expected: no output.

- [ ] **Step 4: Commit**

```bash
git add tests/YandexSearchServiceTest.php
git commit -m "test: update YandexSearchServiceTest to use PSR-18 mocks and Nyholm responses"
```

---

### Task 5: Update `README.md`

**Files:**
- Modify: `README.md`

- [ ] **Step 1: Replace the Requirements section**

Find and replace this block:

```markdown
## Requirements

* PHP 8.1 or higher
* `ext-simplexml`
* A [Guzzle](https://github.com/guzzle/guzzle) HTTP client (`guzzlehttp/guzzle: ^7.8`)
* A PSR-3 logger (`psr/log: ^3.0`)
```

With:

```markdown
## Requirements

* PHP 8.1 or higher
* `ext-simplexml`
* A PSR-18 HTTP client (`psr/http-client: ^1.0`)
* A PSR-17 HTTP factory implementing both `RequestFactoryInterface` and `StreamFactoryInterface` (`psr/http-factory: ^1.0`)
* A PSR-3 logger (`psr/log: ^3.0`)
```

- [ ] **Step 2: Replace the Usage code example**

Find and replace this block inside the `## Usage` section:

```php
// Create a Guzzle client instance (any GuzzleHttp\ClientInterface implementation)
$httpClient = new \GuzzleHttp\Client();

// Create a logger instance (any PSR-3 LoggerInterface implementation)
$logger = new \Psr\Log\NullLogger();

// Initialize the service with your credentials:
//   - Folder ID from your Yandex Cloud account
//   - API key from your Yandex Cloud account
$service = new YandexSearchService($httpClient, $logger, 'abcdefg', 'A1B2C3D4');
```

With:

```php
// Any PSR-17 factory that implements both RequestFactoryInterface and StreamFactoryInterface.
// Popular choices: Nyholm\Psr7\Factory\Psr17Factory, Laminas\Diactoros\RequestFactory, etc.
$factory = new \Nyholm\Psr7\Factory\Psr17Factory();

// Any PSR-18 HTTP client.
// Popular choices: GuzzleHttp\Client, Symfony\Component\HttpClient\Psr18Client, etc.
$httpClient = new \GuzzleHttp\Client();

// Any PSR-3 logger.
$logger = new \Psr\Log\NullLogger();

// Initialize the service with your credentials:
//   - Folder ID from your Yandex Cloud account
//   - API key from your Yandex Cloud account
$service = new YandexSearchService($httpClient, $factory, $logger, 'abcdefg', 'A1B2C3D4');
```

- [ ] **Step 3: Replace the Configuration section example**

Find:

```php
$service = new YandexSearchService($httpClient, $logger, 'abcdefg', 'A1B2C3D4');
```

Replace with:

```php
$service = new YandexSearchService($httpClient, $factory, $logger, 'abcdefg', 'A1B2C3D4');
```

- [ ] **Step 4: Verify no Guzzle references remain in README**

```bash
grep -n -i "guzzle" README.md
```

Expected: no output (or only inside `GuzzleHttp\Client` in the usage comment where it's listed as one of the possible PSR-18 implementations — that is acceptable).

- [ ] **Step 5: Commit**

```bash
git add README.md
git commit -m "docs: update README for PSR-18/PSR-17 migration, remove Guzzle requirement"
```

---

### Task 6: Final verification

**Files:** none

- [ ] **Step 1: Run full test suite one more time**

```bash
composer test
```

Expected: all tests pass.

- [ ] **Step 2: Confirm no Guzzle references remain in production code or docs**

```bash
grep -rn "GuzzleHttp\|guzzlehttp" src/ README.md composer.json
```

Expected: no output.

- [ ] **Step 3: Check static analysis (if configured)**

```bash
vendor/bin/phpstan analyse src/ --level=max 2>&1 | tail -5
```

Expected: `[OK] No errors`.
