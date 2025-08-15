<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Person;

final class PersonTest extends TestCase {

  public function testFullName(): void {
    $name = 'John';
    $surname = 'Doe';
    $gender = 'M';
    $person = new Person();
    $person->setName($name);
    $person->setSurname($surname);
    $person->setGender($gender);
    $this->assertEquals('John Doe', $person->getFullName());
  }

  #[Test]
  public function full_name_is_first_name_if_no_surname(): void {
    $name = 'John';
    $gender = 'M';
    $person = new Person();
    $person->setName($name);
    $person->setGender($gender);
    $this->assertEquals($name, $person->getFullName());
  }
}
