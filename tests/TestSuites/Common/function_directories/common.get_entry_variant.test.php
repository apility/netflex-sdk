<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetEntryVariantTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_directories.php');
  }

  public function testGetEntryVariant (): void
  {
    NF::$capi->mockResponse(
      new Response(200, ['Content-Type' => 'application/json'],
      json_encode([
        'variant'
      ])
    ));

    $this->assertMatchesJsonSnapshot(get_entry_variant(10000));
  }

  public function testThrowsExceptionWhenNotFound(): void
  {
    NF::$capi->mockResponse(new Response(404));

    $this->expectException('GuzzleHttp\Exception\ClientException');
    get_entry_variant(10000);
  }
}

