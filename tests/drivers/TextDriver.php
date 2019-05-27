<?php

use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\Exceptions\CantBeSerialized;

class TextDriver implements Driver
{
  public function serialize($data): string
  {
    $data = $data ?? '';
    if (!is_string($data)) {
      throw new CantBeSerialized('Only strings can be serialized to text');
    }

    return $data;
  }

  public function extension(): string
  {
    return 'txt';
  }

  public function match($expected, $actual)
  {
    Assert::assertEquals($expected, $actual);
  }
}
