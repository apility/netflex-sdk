<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetBlockContentImageTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once(__DIR__ . '/../../../src/functions/common/functions_blocks.php');
    require_once(__DIR__ . '/../../../src/functions/common/functions_system.php');
    require_once(__DIR__ . '/../../../src/functions/live/functions_pages.php');
  }

  public function testOutputsMatchesSnapshot(): void
  {
    global $blockhash;

    NF::$site->mockVariable('site_cdn_protocol', 'https');
    NF::$site->mockVariable('site_cdn_url', 'test-cdn.dev');

    NF::$site->mockContent('test_nfabcdefghijklmno1', [
      'id' => 1,
      'area' => 'test_nfabcdefghijklmno1',
      'image' => 'fdsflpsd'
    ]);

    $blockhash = 'nfabcdefghijklmno1';

    $this->assertMatchesSnapshot(
      get_block_content_image('test', '100x100', 'rc'),
      new HtmlDriver
    );

    $this->assertMatchesSnapshot(
      get_block_content_image('test', '100x100', 'rc', 'testclass'),
      new HtmlDriver
    );

    $this->assertMatchesSnapshot(
      get_block_content_image('test', '100x100', 'fill', 'testclass', '255,255,255'),
      new HtmlDriver
    );

    $this->assertMatchesSnapshot(
      get_block_content_image('test', '100x100', 'fill', 'testclass', '255,255,255', 'testclass2'),
      new HtmlDriver
    );
  }
}
