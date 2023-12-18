<?php

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\Result;

class ResultTest extends TestCase
{
    public function testResultConstructor(): void
    {
        $title = 'Test Title';
        $url = 'https://example.com/path?arg=value#anchor';
        $snippet = 'This is a test snippet.';

        $result = new Result($title, $url, $snippet);

        $this->assertEquals($title, $result->getTitle(), 'Constructor doesn\'t set title properly');
        $this->assertEquals($url, $result->getURL(), 'Constructor doesn\'t set URL properly');
        $this->assertEquals('example.com', $result->getDomain(), 'Fetching domain doesn\'t work properly');
        $this->assertEquals($snippet, $result->getSnippet(), 'Constructor doesn\'t set snippet properly');
    }

    public function testResultConstructorWithNullSnippet(): void
    {
        $title = 'Test Title';
        $url = 'https://example.com/path?arg=value#anchor';

        $result = new Result($title, $url, null);

        $this->assertEquals($title, $result->getTitle(), "Constructor doesn't set title properly.");
        $this->assertEquals($url, $result->getURL(), "Constructor doesn't set URL properly.");
        $this->assertNull($result->getSnippet(), "Constructor should handle null snippet values.");
    }
}
