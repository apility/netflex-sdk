<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_DisplayPageBlocksTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_convert.php');
    require_once('src/functions/common/functions_pages.php');
    require_once('src/functions/common/functions_blocks.php');
  }

  public function testOutputsMatchesSnapshot(): void
  {
    NF::$capi->mockResponse(
      new Response(200, ['Content-Type' => 'application/json'], json_encode(
        [
          [
            'id' => 1,
            'area' => 'sections',
            'type' => 'html',
            'sorting' => null,
            'name' => 'Test 1',
            'title' => 'nfabcdefghijklmno1',
            'text' => 1,
            'published' => 1
          ],
          [
            'id' => 2,
            'area' => 'sections',
            'type' => 'html',
            'sorting' => null,
            'name' => 'Test 2',
            'title' => 'nfabcdefghijklmno2',
            'text' => 1,
            'published' => 0
          ]
        ]
      ))
    );

    NF::$site->mockComponent(1, 'test', 'Test', 'builder');

    $this->assertMatchesSnapshot(
      capture('display_page_blocks', 1, 'sections', ['method' => 'display_page_blocks']),
      new HtmlDriver
    );
  }
}
