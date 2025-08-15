<?php

namespace App;

use PHPUnit\Framework\TestCase;

final class AmountTest extends TestCase {

  public function testGetCents_WithValidCents_ReturnsUnchangedCents(): void {
    $amount = new Amount(30); // Arrange data for the test
    $cents = $amount->getCents(); // Act method we want to test
    $this->assertEquals(30, $cents); // Assert that behaviour is correct.
  }

  public function testConstructor_WithNegativeCents_ThrowsException(): void {
    $this->expectException(\Exception::class);
    $amount = new Amount(-130); // Arrange data for the test
  }

}
