<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetEntryVariants extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_directories.php');
    require_once('src/functions/common/functions_convert.php');
  }

  public function testGetEntryVariants (): void
  {
    NF::$cache->mockItem('entry/10000', [
      'published' => true,
      'variants' => [
        ['variant1'],
        ['variant2']
      ]
    ]);

    $this->assertMatchesJsonSnapshot(
      get_entry_variants(10000)
    );
  }
}

