<?php

declare(strict_types=1);

namespace YandexSearchAPI;

class Correction
{
    private string $sourceText;
    private string $resultText;

    public function __construct(string $sourceText, string $resultText)
    {
        $this->sourceText = $sourceText;
        $this->resultText = $resultText;
    }

    public function getSourceText(): string
    {
        return $this->sourceText;
    }

    public function setSourceText(string $sourceText): void
    {
        $this->sourceText = $sourceText;
    }

    public function getResultText(): string
    {
        return $this->resultText;
    }

    public function setResultText(string $resultText): void
    {
        $this->resultText = $resultText;
    }
}
