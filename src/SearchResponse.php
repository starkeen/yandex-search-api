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

    private int $totalCount = 0;
    private string $totalCountHuman = '';

    private int $page = 0;

    private int $pageSize = 1;

    private ?Correction $correction = null;

    private string $requestID;

    private ?int $errorCode = null;
    private ?string $errorText = null;

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
     * @return array|SearchResultItem[]
     */
    public function getResults(): array
    {
        return $this->results;
    }

    public function isError(): bool
    {
        return $this->errorText !== null;
    }

    public function getErrorText(): ?string
    {
        return $this->errorText;
    }

    public function setErrorText(string $text): void
    {
        $this->errorText = $text;
    }

    public function getErrorCode(): ?int
    {
        return $this->errorCode;
    }

    public function setErrorCode(?int $errorCode): void
    {
        $this->errorCode = $errorCode;
    }


    public function getRequestID(): string
    {
        return $this->requestID;
    }

    public function setRequestID(string $requestID): void
    {
        $this->requestID = $requestID;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function setTotalCount(int $totalCount): void
    {
        $this->totalCount = $totalCount;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function getPagesCount(): int
    {
        return (int) ceil($this->getTotalCount() / $this->getPageSize());
    }

    public function getTotalCountHuman(): string
    {
        return $this->totalCountHuman;
    }

    public function setTotalCountHuman(string $totalCountHuman): void
    {
        $this->totalCountHuman = $totalCountHuman;
    }

    public function getCorrection(): ?Correction
    {
        return $this->correction;
    }

    public function setCorrection(?Correction $correction): void
    {
        $this->correction = $correction;
    }
}
