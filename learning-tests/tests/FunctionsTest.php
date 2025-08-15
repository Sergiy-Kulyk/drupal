<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

require_once 'bootstrap.php';

final class FunctionsTest extends TestCase {

  public static function additionProvider(): array {
    return [
      'two_positive' => [1,4,5],
      'two_negative' => [-2,-3,-5],
      'two_positive_negative' => [3,-2,1],
      'two_positive_zero' => [3,0,3],
    ];
  }

  #[DataProvider('additionProvider')]
  public function testAddIntegers($a, $b, $expected): void {
    $this->assertSame($expected, ctnext_user_add($a,$b));
  }
  public function testAddCommutative(): void {
    $this->assertEquals(ctnext_user_add(2,3), ctnext_user_add(1,4));
  }
}
