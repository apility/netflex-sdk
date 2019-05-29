<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Editor_GetBlockContentStringTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_blocks.php');
    require_once('src/functions/editor/functions_pages.php');
  }

  public function testOutputsMatchesSnapshot(): void
  {
    global $blockhash;

    $blockhash = 'nfabcdefghijklmno1';

    NF::$site->mockContent(
      'title_' . $blockhash,
      ['text' => 'hello world']
    );

    $this->assertMatchesSnapshot(
      get_block_content_string(['alias' => 'title', 'content_field' => 'text']),
      new TextDriver
    );

    NF::$site->mockContent(
      'title_' . $blockhash,
      null
    );

    $this->assertEmpty(
      get_block_content_string(['alias' => 'title', 'content_field' => 'text'])
    );
  }
}
