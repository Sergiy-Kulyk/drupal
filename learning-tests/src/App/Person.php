<?php

namespace App;

class Person {

  protected string $name;
  protected string $surname;
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
  public function getSurname(): string {
    return $this->surname;
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

  /**
   * @param string $surname
   */
  public function setSurname(string $surname): void {
    $this->surname = $surname;
  }

  public function getFullName(): string {
    if (!empty($this->name) && !empty($this->surname)) {
      return $this->name . ' ' . $this->surname;
    }
    elseif (!empty($this->name)) {
      return $this->name;
    }
    elseif (!empty($this->surname)) {
      return $this->surname;
    }
    return 'Name is not set';
  }
}
