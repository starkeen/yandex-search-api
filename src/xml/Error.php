<?php

declare(strict_types=1);

namespace YandexSearchAPI\xml;

use SimpleXMLElement;

class Error extends SimpleXMLElement
{
    public function getMessage(): string
    {
        return (string)$this;
    }

    public function getCode(): int
    {
        return (int)$this->attributes()->code;
    }
}
