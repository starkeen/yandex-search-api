<?php

/** @noinspection MethodShouldBeFinalInspection */

declare(strict_types=1);

namespace YandexSearchAPI;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Throwable;
use YandexSearchAPI\parser\ResultsParser;
use YandexSearchAPI\xml\ResponseRoot;

class YandexSearchService
{
    private const YANDEX_SEARCH_URL = 'https://yandex.ru/search/xml';

    private ClientInterface $httpClient;
    private LoggerInterface $logger;

    private ?string $apiId;
    private ?string $apiKey;

    /**
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param string|null $apiId  Folder ID from your Yandex Cloud account
     * @param string|null $apiKey API key from your Yandex Cloud account
     */
    public function __construct(
        ClientInterface $client,
        LoggerInterface $logger,
        ?string $apiId = null,
        ?string $apiKey = null
    ) {
        $this->httpClient = $client;
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

        try {
            $rawResponse = $this->httpClient->request(
                'POST',
                self::YANDEX_SEARCH_URL,
                [
                    RequestOptions::QUERY => [
                        'folderid' => $this->apiId,
                        'l10n' => $request->getLanguage(),
                        'sortby' => $request->getSort(),
                        'filter' => $request->getFilter(),
                    ],
                    RequestOptions::BODY => $request->getXML(),
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'application/xml',
                        'Authorization' => 'Api-Key ' . $this->apiKey,
                    ],
                ]
            );
        } catch (Throwable $exception) {
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
