<?php

use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Driver;
use IvoPetkov\HTML5DOMDocument;
use Spatie\Snapshots\Exceptions\CantBeSerialized;

class HtmlDriver implements Driver
{
  public function serialize($data): string
  {
    $data = $data ?? '<div/>';

    if (!is_string($data)) {
      throw new CantBeSerialized('Only strings can be serialized to HTML');
    }

    $html = new HTML5DOMDocument();
    $html->loadHTML($data);
    return $html->saveHTML();
  }

  public function extension(): string
  {
    return 'html';
  }

  public function match($expected, $actual)
  {
    Assert::assertEquals($this->serialize($expected), $this->serialize($actual));
  }
}
