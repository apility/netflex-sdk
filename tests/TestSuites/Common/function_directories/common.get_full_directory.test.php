<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetFullDirectoryTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_directories.php');
    require_once('src/functions/common/functions_system.php');
    require_once('src/functions/common/functions_convert.php');
  }

  public function testGetFullDirectory (): void
  {
    $testData = [
      [
        'id' => 10000,
        'name' => 'Test Z',
        'published' => true
      ],
      [
        'id' => 10002,
        'name' => 'Test M',
        'published' => false
      ],
      [
        'id' => 10001,
        'name' => 'Test A',
        'published' => true
      ]
    ];

    $mockResponse = new Response(200, ['Content-Type' => 'application/json'], json_encode($testData));

    NF::$capi->mockResponse($mockResponse);
    $this->assertMatchesJsonSnapshot(
      get_full_directory(10000, ['name' => [SORT_STRING, SORT_DESC]])
    );

    NF::$capi->mockResponse($mockResponse);
    $this->assertMatchesJsonSnapshot(
      get_full_directory(10000, ['name' => [SORT_STRING, SORT_ASC]])
    );
  }
}

