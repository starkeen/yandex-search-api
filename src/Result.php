<?php

declare(strict_types=1);

namespace YandexSearchAPI;

class Result
{
    /**
     * @var string
     */
    private string $title;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var string|null
     */
    private string|null $snippet;

    /**
     * @param string $title
     * @param string $url
     * @param string|null $snippet
     */
    public function __construct(string $title, string $url, ?string $snippet)
    {
        $this->title = $title;
        $this->url = $url;
        $this->snippet = $snippet;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getURL(): string
    {
        return $this->url;
    }

    /**
     * @return string
     * @throws SearchException
     */
    public function getDomain(): string
    {
        $host = parse_url($this->url, PHP_URL_HOST);
        if ($host === false) {
            throw new SearchException('Invalid URL in response');
        }

        return $host;
    }

    /**
     * @return string|null
     */
    public function getSnippet(): ?string
    {
        return $this->snippet;
    }
}
