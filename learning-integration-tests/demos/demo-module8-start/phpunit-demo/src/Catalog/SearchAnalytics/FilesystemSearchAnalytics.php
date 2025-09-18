<?php
declare(strict_types=1);

namespace App\Catalog\SearchAnalytics;

use App\Clock\Clock;
use Symfony\Component\Filesystem\Filesystem;

final class FilesystemSearchAnalytics implements SearchAnalytics
{
    private Filesystem $filesystem;
    private Clock $clock;

    public function __construct(Filesystem $filesystem, Clock $clock)
    {
        $this->filesystem = $filesystem;
        $this->clock = $clock;
    }

    /**
     * @inheritDoc
     */
    public function track(array $searchFilters): void
    {
        $csvLine = implode(',', [
            '"' . $this->clock->now()->format('c') . '"',
            '"' . addslashes(json_encode($searchFilters)) . '"'
        ]);
        $this->filesystem->appendToFile(
            __DIR__ . '/../../../var/search_analytics.csv',
            $csvLine
        );
    }
}
