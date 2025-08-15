<?php

use PHPUnit\Framework\TestCase;
use App\Person;

final class PersonTest extends TestCase {

  public function testFullName(): void {
    $name = 'John Doe';
    $gender = 'M';
    $person = new Person();
    $person->setName($name);
    $person->setGender($gender);
    $this->assertEquals($name, $person->getName());
  }
}
