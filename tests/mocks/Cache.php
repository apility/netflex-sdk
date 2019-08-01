<?php

class MockCache {
  private $items = [];

  public function mockItem ($key, $value) {
    $this->items[$key] = $value;
  }

  public function fetch ($key) {
    if (array_key_exists($key, $this->items)) {
      return $this->items[$key];
    }
  }

  public function save ($key, $value, $ttl = 0) {
    $this->items[$key] = $value;
  }

  public function clear () {
    $this->items = [];
  }
}
