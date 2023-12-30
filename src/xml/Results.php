<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Results extends SimpleXMLElement
{
    public function getGrouping(): Grouping
    {
        return new Grouping($this->grouping->asXML());
    }
}
