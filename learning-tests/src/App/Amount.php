<?php

declare(strict_types=1);

namespace App;

final class Amount {

  private int $cents;

  public function __construct(int $cents) {
    if ($cents < 0) {
      throw new \Exception('Amount cant be less than 0');
    }
    $this->cents = $cents;
  }

  public function getCents(): int {
    return $this->cents;
  }
}
