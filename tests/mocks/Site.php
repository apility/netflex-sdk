<?php

class MockSite {
  /** @var array */
  public $variables = [];

  /** @var array */
  public $labels = [];

  /** @var array */
  public $content = [];

  /** @var array */
  public $templates = [];

  public function mockVariable ($alias, $value) {
    $this->variables[$alias] = $value;
  }

  public function mockLabel ($label, $value, $lang) {
    $this->labels[base64_encode($label)] = [
      $lang => $value
    ];
  }

  public function mockContent ($area, $content) {
    $this->content[$area] = $content;
  }

  public function mockComponent ($id, $alias, $name, $type) {
    $this->templates['components'][$id] = [
      'id' => $id,
      'alias' => $alias,
      'name' => $name,
      'type' => $type
    ];
  }

  public function mockTemplate () {

  }

  public function loadLabels () {
    // This function needs to exist for the test not to fail. We do not actually load labels from the API when running the tests, so this function does nothing.
  }
}
