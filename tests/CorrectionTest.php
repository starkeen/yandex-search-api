<?php

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\Correction;

class CorrectionTest extends TestCase
{
    public function testInitializesCorrectionObject(): void
    {
        $sourceText = 'source';
        $resultText = 'result';

        $correction = new Correction($sourceText, $resultText);

        $this->assertSame($sourceText, $correction->getSourceText());
        $this->assertSame($resultText, $correction->getResultText());
    }

    public function testSetters(): void
    {
        $sourceText = 'source1';
        $resultText = 'result1';

        $correction = new Correction($sourceText, $resultText);

        $newSourceText = 'source2';
        $newResultText = 'result2';
        $correction->setSourceText($newSourceText);
        $correction->setResultText($newResultText);

        $this->assertSame($newSourceText, $correction->getSourceText());
        $this->assertSame($newResultText, $correction->getResultText());
    }
}
