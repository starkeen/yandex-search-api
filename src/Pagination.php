<?php

declare(strict_types=1);

namespace YandexSearchAPI;

class Pagination
{
    private ?int $total = null;
    private ?string $totalHuman = null;

    private ?int $currentPage = null;
    private ?int $pageSize = null;

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getTotalHuman(): ?string
    {
        return $this->totalHuman;
    }

    public function setTotalHuman(?string $totalHuman): void
    {
        $this->totalHuman = $totalHuman;
    }

    public function getCurrentPage(): ?int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPage = $currentPage;
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    public function getPagesCount(): int
    {
        return (int)ceil($this->total / $this->pageSize);
    }
}
