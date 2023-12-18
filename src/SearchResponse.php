<?php

declare(strict_types=1);

namespace YandexSearchAPI;

class SearchResponse
{
    private SearchRequest $request;

    /**
     * @var Result[]
     */
    private array $results;

    private ?Pagination $pagination = null;

    private ?Correction $correction = null;

    private string $requestID;

    public function __construct(SearchRequest $request)
    {
        $this->request = $request;
        $this->results = [];
    }

    public function getRequest(): SearchRequest
    {
        return $this->request;
    }

    public function appendResult(string $title, string $url, string $snippet): void
    {
        $this->results[] = new Result($title, $url, $snippet);
    }

    /**
     * @return array|Result[]
     */
    public function getResults(): array
    {
        return $this->results;
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
}
