<?php

namespace App\App;

class Person {

  protected string $name;
  protected string $gender;

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @return string
   */
  public function getGender() {
    return $this->gender;
  }

  /**
   * @param string $gender
   */
  public function setGender(string $gender): void {
    $this->gender = $gender;
  }

  /**
   * @param string $name
   */
  public function setName(string $name): void {
    $this->name = $name;
  }
}
