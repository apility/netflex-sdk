<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetPageBlocksTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_convert.php');
    require_once('src/functions/common/functions_blocks.php');
  }

  public function testOutputsMatchesSnapshot(): void
  {
    NF::$site->mockComponent(1, 'test', 'Test', 'builder');
    NF::$site->mockContent('sections', [
      [
        'id' => 1,
        'area' => 'sections',
        'type' => 'html',
        'sorting' => null,
        'name' => 'Test 1',
        'title' => 'nfabcdefghijklmno1',
        'text' => 1
      ],
      [
        'id' => 2,
        'area' => 'sections',
        'type' => 'html',
        'sorting' => null,
        'name' => 'Test 2',
        'title' => 'nfabcdefghijklmno2',
        'text' => 1
      ]
    ]);

    $this->assertMatchesSnapshot(
      capture('get_page_blocks', 'sections', ['method' => 'get_page_blocks']),
      new HtmlDriver
    );
  }
}
