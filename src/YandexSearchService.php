<?php

/** @noinspection MethodShouldBeFinalInspection */

declare(strict_types=1);

namespace YandexSearchAPI;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Throwable;
use YandexSearchAPI\parser\ResultsParser;
use YandexSearchAPI\xml\ResponseRoot;

class YandexSearchService
{
    private const YANDEX_SEARCH_URL = 'https://yandex.ru/search/xml';

    private Client $httpClient;
    private LoggerInterface $logger;

    private ?string $apiId = null;
    private ?string $apiKey = null;

    /**
     * @param Client $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->httpClient = $client;
        $this->logger = $logger;
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
            $rawResponse = $this->httpClient->post(
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
        }

        $xmlResponse = $xml->getResponse();

        return (new ResultsParser())->parse($request, $xmlResponse);
    }

    /**
     * @param string $apiId
     * @return void
     */
    public function setApiId(string $apiId): void
    {
        $this->apiId = $apiId;
    }

    /**
     * @param string $apiKey
     * @return void
     */
    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
