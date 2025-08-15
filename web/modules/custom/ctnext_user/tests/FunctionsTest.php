<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once 'bootstrap.php';

final class FunctionsTest extends TestCase {
  public function testAddPositiveIntegers(): void {
    $this->assertEquals(5, ctnext_user_add(1,4));
  }
  public function testAddNegativeIntegers(): void {
    $this->assertEquals(-5, ctnext_user_add(-2,-3));
  }
  public function testAddCommutative(): void {
    $this->assertEquals(ctnext_user_add(2,3), ctnext_user_add(1,4));
  }
}
