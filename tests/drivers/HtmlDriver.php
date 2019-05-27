<?php

use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\Exceptions\CantBeSerialized;

class HtmlDriver implements Driver
{
  public function serialize($data): string
  {
    $data = $data ?? '<div/>';
    if (!is_string($data)) {
      throw new CantBeSerialized('Only strings can be serialized to HTML');
    }

    $html = new DOMDocument();
    $html->loadHTML($data);
    $html->formatOutput = true;

    return $html->saveHTML();
  }

  public function extension(): string
  {
    return 'html';
  }

  public function match($expected, $actual)
  {
    Assert::assertEquals($expected, $this->serialize($actual));
  }
}
