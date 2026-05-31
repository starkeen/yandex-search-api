<?php

declare(strict_types=1);

namespace YandexSearchAPI\Tests;

use PHPUnit\Framework\TestCase;
use YandexSearchAPI\Pagination;

class PaginationTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $pagination = new Pagination();

        $pagination->setTotal(12345);
        $pagination->setTotalHuman('Found 12345 items');
        $pagination->setCurrentPage(2);
        $pagination->setPageSize(20);

        $this->assertEquals(12345, $pagination->getTotal());
        $this->assertEquals('Found 12345 items', $pagination->getTotalHuman());
        $this->assertEquals(2, $pagination->getCurrentPage());
        $this->assertEquals(20, $pagination->getPageSize());

        $this->assertEquals(618, $pagination->getPagesCount());
    }

    public function testPagesCountIsZeroWhenTotalNotSet(): void
    {
        $pagination = new Pagination();
        $pagination->setPageSize(20);

        $this->assertEquals(0, $pagination->getPagesCount());
    }

    public function testPagesCountIsZeroWhenPageSizeNotSet(): void
    {
        $pagination = new Pagination();
        $pagination->setTotal(12345);

        $this->assertEquals(0, $pagination->getPagesCount());
    }

    public function testPagesCountIsZeroWhenPageSizeIsZero(): void
    {
        $pagination = new Pagination();
        $pagination->setTotal(12345);
        $pagination->setPageSize(0);

        $this->assertEquals(0, $pagination->getPagesCount());
    }
}
