<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetCurrentUrlTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_system.php');
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
    $_SERVER['HTTP_HOST'] = 'netflex-sdk.dev';
    $_SERVER['REQUEST_URI'] = '/test';
  }

  public function testGetCurrentUrl(): void
  {
    $this->assertMatchesSnapshot(get_current_url(), new TextDriver);
  }

  public function testHandlesSpecialChars(): void {
    $_SERVER['REQUEST_URI'] = '/test-æøå';
    $this->assertMatchesSnapshot(get_current_url(), new TextDriver);
  }
}
