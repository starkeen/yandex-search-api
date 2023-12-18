<?php

declare(strict_types=1);

namespace YandexSearchAPI;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;

class YandexSearchService
{
    private const YANDEX_SEARCH_URL = 'https://yandex.ru/search/xml';

    private Client $httpClient;
    private LoggerInterface $logger;

    private ?string $apiId = null;
    private ?string $apiKey = null;

    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->httpClient = $client;
        $this->logger = $logger;
    }

    public function search(SearchRequest $request): SearchResponse
    {
        if ($this->apiId === null || $this->apiKey === null) {
            throw new ConfigurationException('API ID and API key must be set');
        }

        $result = new SearchResponse($request);

        try {
            $rawResponse = $this->httpClient->post(
                self::YANDEX_SEARCH_URL,
                [
                    RequestOptions::QUERY => [
                        'folderid' => $this->apiId,
                        'l10n' => 'ru',
                        'sortby' => 'rlv',
                        'filter' => 'none',
                    ],
                    RequestOptions::BODY => $request->getXML(),
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'application/xml',
                        'Authorization' => 'Api-Key ' . $this->apiKey,
                    ],
                ]
            );
        } catch (TransferException $exception) {
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
            $xml = new SimpleXMLElement($rawResponse->getBody()->getContents());
        } catch (Exception $exception) {
            $this->logger->error(
                'Yandex search API response parse error',
                [
                    'request' => $request,
                ]
            );

            throw new SearchException('Yandex search API response parse error', 0, $exception);
        }

        $pagination = new Pagination();
        $xmlResponse = $xml->response;
        $result->setRequestID((string)$xmlResponse->reqid);

        if (property_exists($xmlResponse, 'error')) {
            $result->setErrorText((string)$xmlResponse->error);
            return $result;
        }

        if (property_exists($xmlResponse, 'misspell')) {
            $misspell = $xmlResponse->misspell;
            $sourceText = strip_tags($misspell->{'source-text'}->saveXML());
            $correction = new Correction($sourceText, (string)$misspell->text);
            $result->setCorrection($correction);
        }

        $pagination->setTotal((int)($xmlResponse->results->grouping->found[0] ?? 0));
        $pagination->setTotalHuman((string)$xmlResponse->results->grouping->{'found-docs-human'});
        $pagination->setPageSize((int)$xmlResponse->results->grouping->attributes()['groups-on-page']);
        $pagination->setCurrentPage((int)$xmlResponse->results->grouping->page);
        $result->setPagination($pagination);

        foreach ($xml->response->results->grouping->group as $group) {
            foreach ($group->doc as $doc) {
                $passage = $doc->passages->passage;
                $result->appendResult(
                    strip_tags($doc->title->saveXML()),
                    (string)$doc->url,
                    $passage !== null ? strip_tags($passage->saveXML()) : ''
                );
            }
        }

        return $result;
    }

    public function setApiId(string $apiId): void
    {
        $this->apiId = $apiId;
    }

    public function setApiKey(string $apiKey): void
    {
        $this->apiKey = $apiKey;
    }
}
