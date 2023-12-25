<?php

declare(strict_types=1);

namespace YandexSearchAPI;

class Correction
{
    /**
     * @var string
     */
    private string $sourceText;

    /**
     * @var string
     */
    private string $resultText;

    /**
     * @param string $sourceText
     * @param string $resultText
     */
    public function __construct(string $sourceText, string $resultText)
    {
        $this->sourceText = $sourceText;
        $this->resultText = $resultText;
    }

    /**
     * @return string
     */
    public function getSourceText(): string
    {
        return $this->sourceText;
    }

    /**
     * @param string $sourceText
     * @return void
     */
    public function setSourceText(string $sourceText): void
    {
        $this->sourceText = $sourceText;
    }

    /**
     * @return string
     */
    public function getResultText(): string
    {
        return $this->resultText;
    }

    /**
     * @param string $resultText
     * @return void
     */
    public function setResultText(string $resultText): void
    {
        $this->resultText = $resultText;
    }
}
