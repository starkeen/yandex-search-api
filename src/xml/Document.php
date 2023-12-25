<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Document extends SimpleXMLElement
{
    public function getPlainTitle(): string
    {
        return strip_tags($this->title->asXML());
    }

    public function getUrl(): string
    {
        return (string) $this->url;
    }

    /**
     * @return Passage[]
     */
    public function getPassages(): array
    {
        $passages = [];

        foreach ($this->passages->passage as $passage) {
            $passages[] = new Passage($passage->asXML());
        }

        return $passages;
    }
}
