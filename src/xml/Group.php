<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Group extends SimpleXMLElement
{
    /**
     * @return Document[]
     */
    public function getDocuments(): array
    {
        $documents = [];

        foreach ($this->doc as $doc) {
            $documents[] = new Document($doc->asXML());
        }

        return $documents;
    }
}
