<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetEntryDataTest extends TestCase
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

  public function testGetEntryData (): void
  {
    NF::$cache->mockItem('entry/10000', [
      'id' => 10000,
      'name' => 'Test 1',
      'url' => 'test-1/',
      'revision' => 10000,
      'published' => true
    ]);

    $this->assertEquals(
      'Test 1',
      get_entry_data(10000, 'name')
    );
  }

}
