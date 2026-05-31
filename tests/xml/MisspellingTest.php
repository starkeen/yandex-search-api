<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests\xml;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\xml\Mispelling;
use YandexSearchAPI\xml\Misspelling;

class MisspellingTest extends TestCase
{
    private const XML = '<misspell><source-text>yande<hlword>xx</hlword></source-text><text>yandex</text></misspell>';

    public function testReadsSourceAndResultText(): void
    {
        $misspell = new Misspelling(self::XML);

        $this->assertEquals('yandexx', $misspell->getSourceText());
        $this->assertEquals('yandex', $misspell->getResultText());
    }

    public function testDeprecatedAliasStillWorks(): void
    {
        $misspell = new Mispelling(self::XML);

        $this->assertInstanceOf(Misspelling::class, $misspell);
        $this->assertEquals('yandexx', $misspell->getSourceText());
        $this->assertEquals('yandex', $misspell->getResultText());
    }
}
