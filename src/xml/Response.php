<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Response extends SimpleXMLElement
{
    /**
     * @return string
     */
    public function getRequestID(): string
    {
        return (string)$this->reqid;
    }

    /**
     * @return Error|null
     */
    public function getError(): ?Error
    {
        if (isset($this->error)) {
            return new Error($this->error->asXML());
        }

        return null;
    }

    /**
     * @return Misspelling|null
     */
    public function getMisspelling(): ?Misspelling
    {
        if (isset($this->misspell)) {
            return new Misspelling($this->misspell->asXML());
        }

        return null;
    }

    /**
     * @return Results|null
     */
    public function getResults(): ?Results
    {
        if (isset($this->results)) {
            return new Results($this->results->asXML());
        }

        return null;
    }
}
