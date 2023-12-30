<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Passage extends SimpleXMLElement
{
    public function getText(): string
    {
        $xml = (string)$this->asXML();
        $text = html_entity_decode($xml, ENT_QUOTES, 'UTF-8');

        return strip_tags($text);
    }
}
