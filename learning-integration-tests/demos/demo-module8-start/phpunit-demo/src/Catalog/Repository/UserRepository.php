<?php
declare(strict_types=1);

namespace App\Catalog\Repository;

/**
 * This interface is simplified for the sake of brief examples.
 */
interface UserRepository
{
    public function findUsersInArea(string $area): array;

    public function findUsersInterestedInArtist(string $artistName): array;
}
