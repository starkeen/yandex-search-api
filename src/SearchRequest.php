<?php

declare(strict_types=1);

namespace YandexSearchAPI;

use SimpleXMLElement;

class SearchRequest
{
    private const DEFAULT_MAX_RESULTS = 10;
    private const DEFAULT_MAX_PASSAGES = 4;

    private string $query;

    private int $numResults = self::DEFAULT_MAX_RESULTS;

    private int $page = 0;

    private int $maxPassages = self::DEFAULT_MAX_PASSAGES;

    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getNumResults(): int
    {
        return $this->numResults;
    }

    public function setNumResults(int $numResults): void
    {
        $this->numResults = $numResults;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getMaxPassages(): int
    {
        return $this->maxPassages;
    }

    public function setMaxPassages(int $maxPassages): void
    {
        $this->maxPassages = $maxPassages;
    }

    public function getXML(): string
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><request></request>');
        $xml->addChild('query', $this->getQuery());
        $xml->addChild('page', (string) $this->getPage());
        $xml->addChild('maxpassages', (string) $this->getMaxPassages());

        $groupings = $xml->addChild('groupings', '');
        $groupBy = $groupings->addChild('groupby', '');
        $groupBy->addAttribute('mode', 'flat');
        $groupBy->addAttribute('groups-on-page', (string) $this->getNumResults());
        $groupBy->addAttribute('docs-in-group', '1');

        return $xml->saveXML();
    }
}
