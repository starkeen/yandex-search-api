<?php

declare(strict_types=1);

namespace YandexSearchAPI\dto;

use YandexSearchAPI\Result;

class ResultsCollection
{
    /**
     * @var Result[]
     */
    private array $results = [];

    /**
     * @param Result $result
     * @return void
     */
    public function appendResult(Result $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return Result[]
     */
    public function getResults(): array
    {
        return $this->results;
    }
}
