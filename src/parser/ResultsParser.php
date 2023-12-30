<?php

declare(strict_types=1);

namespace YandexSearchAPI\parser;

use YandexSearchAPI\Correction;
use YandexSearchAPI\dto\ResultsCollection;
use YandexSearchAPI\Pagination;
use YandexSearchAPI\Result;
use YandexSearchAPI\SearchException;
use YandexSearchAPI\SearchRequest;
use YandexSearchAPI\SearchResponse;
use YandexSearchAPI\xml\Grouping;
use YandexSearchAPI\xml\Passage;
use YandexSearchAPI\xml\Response;

class ResultsParser
{
    /**
     * @param SearchRequest $request
     * @param Response $response
     * @return SearchResponse
     */
    public function parse(SearchRequest $request, Response $response): SearchResponse
    {
        $result = new SearchResponse($request);

        $result->setRequestID($response->getRequestID());

        $error = $response->getError();
        if ($error !== null) {
            throw new SearchException($error->getMessage(), $error->getCode());
        }

        $mispelling = $response->getMisspelling();
        if ($mispelling !== null) {
            $correction = new Correction($mispelling->getSourceText(), $mispelling->getResultText());
            $result->setCorrection($correction);
        }

        $result->setPagination($this->buildPagination($response->getResults()->getGrouping()));

        $collection = new ResultsCollection();
        foreach ($response->getResults()->getGrouping()->getGroups() as $group) {
            foreach ($group->getDocuments() as $doc) {
                $passages = $doc->getPassages();

                $resultItem = new Result(
                    $doc->getPlainTitle(),
                    $doc->getUrl(),
                    $this->getPassageText($passages)
                );

                $collection->appendResult($resultItem);
            }
        }
        $result->setResultsCollection($collection);

        return $result;
    }

    /**
     * @param Grouping $grouping
     * @return Pagination
     */
    private function buildPagination(Grouping $grouping): Pagination
    {
        $pagination = new Pagination();

        $pagination->setTotal($grouping->getFound());
        $pagination->setTotalHuman($grouping->getFoundDocsHuman());
        $pagination->setPageSize($grouping->getGroupsOnPage());
        $pagination->setCurrentPage($grouping->getCurrentPage());

        return $pagination;
    }

    /**
     * @param Passage[] $passages
     * @return string
     */
    private function getPassageText(array $passages): string
    {
        $result = '';

        foreach ($passages as $passage) {
            $result .= ' ' . $passage->getText();
        }

        return trim($result);
    }
}
