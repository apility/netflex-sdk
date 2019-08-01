<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Common_GetRedirectTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_system.php');
    require_once('src/functions/common/functions_convert.php');
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
    $_SERVER['HTTP_HOST'] = 'netflex-sdk.dev';
  }

  public function testGetRedirect(): void
  {
    $redirects = json_encode([
      [
        'id' => 1,
        'source_url' => 'test',
        'target_url' => 'test_value'
      ]
    ]);

    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], '[]'));
    $this->assertEquals(
      0,
      get_redirect('test', 'target_url')
    );

    NF::$cache->clear();

    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], $redirects));
    $this->assertEquals(
      'test_value',
      get_redirect('test', 'target_url')
    );
  }

  public function testGetRedirectFromCache(): void
  {
    NF::$cache->mockItem('redirects', [
      [
        'id' => 1,
        'source_url' => 'test_cache',
        'target_url' => 'test_value'
      ]
    ]);

    $this->assertEquals(
      'test_value',
      get_redirect('test_cache', 'target_url')
    );
  }
}
