<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Request extends SimpleXMLElement
{
    public function getQuery(): string
    {
        return (string)$this->query;
    }
}
