<?php

use PHPUnit\Framework\TestCase;

final class CreateSecretKeyTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once(__DIR__ . '/../../src/functions/common/functions_system.php');
  }

  public function testCreateSecretKey(): void
  {
    $key = create_secret_key();
    $this->assertIsString($key);
    $this->assertStringMatchesFormat('%x', $key);
    $this->assertEquals(
      32,
      strlen($key)
    );
  }
}
