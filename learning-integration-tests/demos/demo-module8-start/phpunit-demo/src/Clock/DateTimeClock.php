<?php
declare(strict_types=1);

namespace App\Clock;

use DateTimeImmutable;
use DateTimeZone;

final class DateTimeClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
