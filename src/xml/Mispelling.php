<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Mispelling extends SimpleXMLElement
{
    public function getSourceText(): string
    {
        return strip_tags($this->{'source-text'}->saveXML());
    }

    public function getResultText(): string
    {
        return strip_tags($this->{'text'}->saveXML());
    }
}
