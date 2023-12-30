<?php

declare(strict_types=1);

namespace YandexSearchAPI;

use SimpleXMLElement;
use YandexSearchAPI\constant\Filter;
use YandexSearchAPI\constant\Language;
use YandexSearchAPI\constant\Sort;

class SearchRequest
{
    private const DEFAULT_MAX_RESULTS = 10;
    private const DEFAULT_MAX_PASSAGES = 4;

    /**
     * @var string search query
     */
    private string $query;

    /**
     * @var int count of results per page
     */
    private int $numResults = self::DEFAULT_MAX_RESULTS;

    /**
     * @var int page number, starting from 0
     */
    private int $page = 0;

    /**
     * @var int count of passages per document
     */
    private int $maxPassages = self::DEFAULT_MAX_PASSAGES;

    /**
     * @var string language of search results, enum Language
     */
    private string $language = Language::RUSSIAN;

    /**
     * @var string sort order of search results, enum Sort
     */
    private string $sort = Sort::RELEVANCE;

    /**
     * @var string filter of search results, enum Filter
     */
    private string $filter = Filter::NONE;

    /**
     * @param string $query
     */
    public function __construct(string $query)
    {
        $this->query = $query;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return int
     */
    public function getNumResults(): int
    {
        return $this->numResults;
    }

    /**
     * @param int $numResults
     * @return void
     */
    public function setNumResults(int $numResults): void
    {
        $this->numResults = $numResults;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return void
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getMaxPassages(): int
    {
        return $this->maxPassages;
    }

    /**
     * @param int $maxPassages
     * @return void
     */
    public function setMaxPassages(int $maxPassages): void
    {
        $this->maxPassages = $maxPassages;
    }

    /**
     * @return string
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return void
     */
    public function setLanguage(string $language): void
    {
        if (!in_array($language, [Language::RUSSIAN, Language::ENGLISH, Language::TURKISH], true)) {
            throw new ConfigurationException('Invalid language');
        }

        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param string $sort
     * @return void
     */
    public function setSort(string $sort): void
    {
        if (!in_array($sort, [Sort::RELEVANCE, Sort::DATE], true)) {
            throw new ConfigurationException('Invalid sort');
        }

        $this->sort = $sort;
    }

    /**
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     * @param string $filter
     * @return void
     */
    public function setFilter(string $filter): void
    {
        if (!in_array($filter, [Filter::NONE, Filter::MODERATE, Filter::STRICT], true)) {
            throw new ConfigurationException('Invalid filter');
        }

        $this->filter = $filter;
    }

    /**
     * @return string
     */
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
