<?php
declare(strict_types=1);

namespace App\Catalog\Value;

use App\Catalog\Exception\AmountBelowZero;

final class Amount
{
    private int $cents;

    public function __construct(int $cents)
    {
        if ($cents < 0) {
            throw AmountBelowZero::fromInt($cents);
        }
        $this->cents = $cents;
    }

    public function getCents(): int
    {
        return $this->cents;
    }
}
