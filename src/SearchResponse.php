<?php

declare(strict_types=1);

namespace YandexSearchAPI;

use YandexSearchAPI\dto\ResultsCollection;

class SearchResponse
{
    private SearchRequest $request;

    private ?ResultsCollection $resultsCollection = null;

    private ?Pagination $pagination = null;

    private ?Correction $correction = null;

    private string $requestID;

    public function __construct(SearchRequest $request)
    {
        $this->request = $request;
    }

    public function getRequest(): SearchRequest
    {
        return $this->request;
    }

    /**
     * @return array|Result[]
     */
    public function getResults(): array
    {
        return $this->resultsCollection !== null ? $this->resultsCollection->getResults() : [];
    }

    public function getRequestID(): string
    {
        return $this->requestID;
    }

    public function setRequestID(string $requestID): void
    {
        $this->requestID = $requestID;
    }

    public function getCorrection(): ?Correction
    {
        return $this->correction;
    }

    public function setCorrection(?Correction $correction): void
    {
        $this->correction = $correction;
    }

    public function getPagination(): ?Pagination
    {
        return $this->pagination;
    }

    public function setPagination(?Pagination $pagination): void
    {
        $this->pagination = $pagination;
    }

    public function setResultsCollection(ResultsCollection $resultsCollection): void
    {
        $this->resultsCollection = $resultsCollection;
    }
}
