# Design: Migrate from Guzzle to PSR-18/PSR-17

**Date:** 2026-05-31  
**Version bump:** 2.x → 3.0 (breaking change)

## Goal

Remove `guzzlehttp/guzzle` as a hard dependency. The library will depend on standard PSR interfaces only, letting consumers bring their own HTTP implementation.

## Dependencies

### Remove from `require`
- `guzzlehttp/guzzle: ^7.8`

### Add to `require`
- `psr/http-client: ^1.0` — PSR-18 (`Psr\Http\Client\ClientInterface`)
- `psr/http-factory: ^1.0` — PSR-17 (`RequestFactoryInterface`, `StreamFactoryInterface`)

### Add to `require-dev`
- `nyholm/psr7: ^1.8` — lightweight PSR-7/PSR-17 implementation for tests

## Production Code — `YandexSearchService`

### Constructor (breaking change)

**Before:**
```php
use GuzzleHttp\ClientInterface;

public function __construct(
    ClientInterface $client,
    LoggerInterface $logger,
    ?string $apiId = null,
    ?string $apiKey = null,
)
```

**After:**
```php
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

public function __construct(
    ClientInterface $client,
    RequestFactoryInterface&StreamFactoryInterface $factory,
    LoggerInterface $logger,
    ?string $apiId = null,
    ?string $apiKey = null,
)
```

The intersection type `RequestFactoryInterface&StreamFactoryInterface` is supported in PHP 8.1+, which is already the minimum required version. All major PSR-17 implementations (Nyholm, Guzzle PSR-7, Laminas, Slim PSR-7) implement both interfaces in one class, so consumers pass a single object.

### `search()` method — building the PSR-7 request

Replace the Guzzle-style call with explicit PSR-7 construction:

```php
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

$rawResponse = $this->httpClient->sendRequest($psrRequest);
```

`$rawResponse->getBody()->getContents()` remains unchanged — both Guzzle's and PSR-7's `ResponseInterface` share this method.

### Error handling

PSR-18 throws `Psr\Http\Client\ClientExceptionInterface` on transport errors (instead of Guzzle's `GuzzleException`). The catch block must be updated:

```php
} catch (\Psr\Http\Client\ClientExceptionInterface $exception) {
```

## Tests

### `AbstractTestCase.php`

- Remove all `use GuzzleHttp\*` imports
- Replace `getHttpClientMock()` return type: `GuzzleHttp\ClientInterface` → `Psr\Http\Client\ClientInterface`
- Add `getHttpFactoryMock()` helper returning a mock of `RequestFactoryInterface&StreamFactoryInterface`
- Replace `GuzzleHttp\Psr7\Response` / `GuzzleHttp\Psr7\Stream` with `Nyholm\Psr7\Response` / `Nyholm\Psr7\Stream`

### `YandexSearchServiceTest.php`

- Pass the factory mock as second constructor argument in every test
- Replace `$httpClient->method('request')` with `$httpClient->method('sendRequest')`
- The factory mock must return a valid `RequestInterface` from `createRequest()` and a valid `StreamInterface` from `createStream()` — use real Nyholm objects for these, or chain mock returns

## README

### Requirements section

**Remove:**
```
* A [Guzzle](https://github.com/guzzle/guzzle) HTTP client (`guzzlehttp/guzzle: ^7.8`)
```

**Add:**
```
* A PSR-18 HTTP client (`psr/http-client: ^1.0`)
* A PSR-17 HTTP factory (`psr/http-factory: ^1.0`) implementing both `RequestFactoryInterface` and `StreamFactoryInterface`
```

### Usage section

Replace the Guzzle-specific example with a Nyholm-based example (Nyholm is the lightest option; any PSR-17/PSR-18 pair works):

```php
// Any PSR-17 factory that implements both RequestFactoryInterface and StreamFactoryInterface
$factory = new \Nyholm\Psr7\Factory\Psr17Factory();

// Any PSR-18 client — here using Symfony HTTP Client as an example
$httpClient = new \Symfony\Component\HttpClient\Psr18Client();
// or: $httpClient = new \GuzzleHttp\Client(); // Guzzle still works as a PSR-18 client

$service = new YandexSearchService($httpClient, $factory, $logger, 'folderid', 'apikey');
```

## Out of Scope

- No changes to `SearchRequest`, `SearchResponse`, parsers, or XML layer
- No changes to public query/filter/sort/language enums or value objects
- No deprecation path — this is a clean breaking change in v3.0
