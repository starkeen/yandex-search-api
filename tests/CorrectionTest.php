<?php

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\Correction;

class CorrectionTest extends TestCase
{
    /** @test */
    public function testInitializesCorrectionObject(): void
    {
        $sourceText = 'source';
        $resultText = 'result';

        $correction = new Correction($sourceText, $resultText);

        $this->assertSame($sourceText, $correction->getSourceText());
        $this->assertSame($resultText, $correction->getResultText());
    }
}
