<?php
declare(strict_types=1);

namespace App\Catalog\SearchAnalytics;

interface SearchAnalytics
{
    /**
     * @param array<string, string> $searchFilters
     */
    public function track(array $searchFilters): void;
}
