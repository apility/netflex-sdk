<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Common_GetEntryIdExtended extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_directories.php');
  }

  public function testGetEntryIdExtended (): void
  {
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'hits' => [
        'hits' => [
          ['_source' =>
            [
              'id' => 10000
            ]
          ]
        ]
      ]
    ])));

    $this->assertEquals(
      10000,
      get_entry_id_extended('', '')
    );
  }

}
