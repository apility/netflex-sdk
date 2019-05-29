<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\Snapshots\Drivers\JsonDriver;

final class Common_GetBlockContentListTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_blocks.php');
  }

  public function testOutputsMatchesSnapshot(): void
  {
    global $blockhash;

    $blockhash = 'nfabcdefghijklmno1';

    NF::$site->mockContent('sections_' . $blockhash, [
      ['text' => 1],
      ['text' => 2]
    ]);

    $this->assertMatchesSnapshot(
      get_block_content_list(['alias' => 'sections', 'content_field' => 'text']),
      new JsonDriver
    );

    NF::$site->mockContent('sections_' . $blockhash, [
      ['html' => '<h1>1</h1>'],
      ['html' => '<h1>2</h1>']
    ]);

    $this->assertMatchesSnapshot(
      get_block_content_list(['alias' => 'sections', 'content_field' => 'html']),
      new JsonDriver
    );

    NF::$site->mockContent('sections_' . $blockhash, null);

    $this->assertEmpty(
      get_block_content_list(['alias' => 'sections', 'content_field' => 'text'])
    );
  }
}
