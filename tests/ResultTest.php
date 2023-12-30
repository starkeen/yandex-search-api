<?php

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\Result;
use YandexSearchAPI\SearchException;

class ResultTest extends TestCase
{
    public function testResultConstructor(): void
    {
        $title = 'Test Title';
        $url = 'https://example.com/path?arg=value#anchor';
        $snippet = 'This is a test snippet.';

        $result = new Result($title, $url, $snippet);

        $this->assertEquals($title, $result->getTitle());
        $this->assertEquals($url, $result->getURL());
        $this->assertEquals('example.com', $result->getDomain());
        $this->assertEquals($snippet, $result->getSnippet());
    }

    public function testResultWithNullSnippet(): void
    {
        $title = 'Test Title';
        $url = 'https://example.com/path?arg=value#anchor';

        $result = new Result($title, $url, null);

        $this->assertEquals($title, $result->getTitle());
        $this->assertEquals($url, $result->getURL());
        $this->assertNull($result->getSnippet());
    }

    public function testResultWithWrongDomain(): void
    {
        $title = 'Test Title';
        $url = 'https://';

        $result = new Result($title, $url, null);

        $this->assertEquals($title, $result->getTitle());
        $this->assertEquals($url, $result->getURL());

        $this->expectException(SearchException::class);
        $result->getDomain();
    }
}
