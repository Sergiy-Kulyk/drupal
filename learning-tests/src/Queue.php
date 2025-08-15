<?php

declare(strict_types=1);

class Queue {
  private $items = [];

  public function push($item): void {
    $this->items[] = $item;
  }

  public function pop() {
    if (empty($this->items)) {
      throw new \UnexpectedValueException('Empty queue.');
    }
    return array_shift($this->items);
  }

  public function getSize(): int {
    return count($this->items);
  }

}
