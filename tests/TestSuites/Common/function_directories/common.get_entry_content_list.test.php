<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetEntryContentListTest extends TestCase
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

  public function testGetEntryContentList (): void
  {
    NF::$cache->mockItem('entry/10000', [
      'published' => true,
      'gallery' => [
        ['image' => 'image-1.png'],
        ['image' => 'image-2.png'],
        ['image' => 'image-3.png']
      ]
    ]);

    $this->assertMatchesJsonSnapshot(
      get_entry_content_list(10000, 'gallery', 'image')
    );
  }

}
