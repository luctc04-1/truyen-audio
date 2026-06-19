<?php

namespace App\Shared\DTOs;

class PaginationDTO
{
    /**
     * The page number
     *
     * @var int
     */
    public $page;

    /**
     * The number of items per page
     *
     * @var int
     */
    public $perPage;

    /**
     * The sort field
     *
     * @var string|null
     */
    public $sortBy;

    /**
     * The sort direction
     *
     * @var string
     */
    public $sortDirection;

    /**
     * Create a new DTO instance
     *
     * @param  int  $page
     * @param  int  $perPage
     * @param  string|null  $sortBy
     * @param  string  $sortDirection
     */
    public function __construct(int $page = 1, int $perPage = 15, ?string $sortBy = null, string $sortDirection = 'asc')
    {
        $this->page = max(1, $page);
        $this->perPage = min($perPage, 100);
        $this->sortBy = $sortBy;
        $this->sortDirection = $sortDirection;
    }

    /**
     * Get the offset
     *
     * @return int
     */
    public function offset()
    {
        return ($this->page - 1) * $this->perPage;
    }
}
