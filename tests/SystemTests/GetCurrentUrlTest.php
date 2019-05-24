<?php

use PHPUnit\Framework\TestCase;

final class GetCurrentUrlTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once(__DIR__ . '/../../src/functions/common/functions_system.php');
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
    $_SERVER['HTTP_HOST'] = 'netflex-sdk.dev';
    $_SERVER['REQUEST_URI'] = '/test';
  }

  public function testGetCurrentUrl(): void
  {
    $this->assertEquals(
      'https://netflex-sdk.dev/test',
      get_current_url()
    );

    $_SERVER['REQUEST_URI'] = '/test-æøå';

    $this->assertEquals(
      'https://netflex-sdk.dev/test-&aelig;&oslash;&aring;',
      get_current_url()
    );
  }
}
