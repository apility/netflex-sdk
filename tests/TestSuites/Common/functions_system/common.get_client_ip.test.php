<?php

use PHPUnit\Framework\TestCase;

final class Common_GetClientIpTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_system.php');
  }

  public function testGetClientIp(): void
  {
    $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
    $this->assertEquals('1.1.1.1', get_client_ip());
  }

  public function testHandlesNull(): void
  {
    $_SERVER['REMOTE_ADDR'] = null;
    $this->assertEquals('UNKNOWN', get_client_ip());
  }
}
