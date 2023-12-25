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
     * @return Mispelling|null
     */
    public function getMisspelling(): ?Mispelling
    {
        if (isset($this->misspell)) {
            return new Mispelling($this->misspell->asXML());
        }

        return null;
    }

    /**
     * @return Results
     */
    public function getResults(): Results
    {
        return new Results($this->results->asXML());
    }
}
