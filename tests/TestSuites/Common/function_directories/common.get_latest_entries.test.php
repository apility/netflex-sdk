<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetLatestEntriesTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_directories.php');
  }

  public function testGetLatestEntries (): void
  {
    $testData = [
      ['_source' => ['id' => 10000]],
      ['_source' => ['id' => 10001]],
      ['_source' => ['id' => 10002]],
    ];

    $mockResponse = new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'hits' => [
        'hits' => $testData
      ]
    ]));

    NF::$capi->mockResponse($mockResponse);
    $this->assertMatchesJsonSnapshot(
      get_latest_entries(10000, '')
    );
  }
}
