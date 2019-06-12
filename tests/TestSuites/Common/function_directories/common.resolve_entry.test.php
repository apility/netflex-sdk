<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Common_ResolveEntryTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_directories.php');
  }

  public function testResolveEntry (): void
  {
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode(10000)));

    $this->assertEquals(
      10000,
      get_entry_id('test/')
    );
  }
}
