<?php

declare(strict_types=1);

namespace YandexSearchAPI;

class Result
{
    private string $title;

    private string $url;

    private string|null $snippet;

    public function __construct(string $title, string $url, ?string $snippet)
    {
        $this->title = $title;
        $this->url = $url;
        $this->snippet = $snippet;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getURL(): string
    {
        return $this->url;
    }

    public function getDomain(): string
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    public function getSnippet(): ?string
    {
        return $this->snippet;
    }
}
