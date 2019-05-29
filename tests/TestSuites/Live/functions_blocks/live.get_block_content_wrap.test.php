<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Live_GetBlockContentWrapTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_blocks.php');
    require_once('src/functions/live/functions_pages.php');
  }

  public function testOutputsMatchesSnapshot(): void
  {
    global $blockhash;

    NF::$site->mockContent('test_nfabcdefghijklmno1', [
        'id' => 1,
        'area' => 'test_nfabcdefghijklmno1',
        'html' => '<h1>Foo.Bar</h1>'
    ]);

    $blockhash = 'nfabcdefghijklmno1';

    $this->assertMatchesSnapshot(
      get_block_content_wrap('test', 'div'),
      new HtmlDriver
    );

    $this->assertMatchesSnapshot(
      get_block_content_wrap('test', 'div', 'testclass'),
      new HtmlDriver
    );
  }
}
