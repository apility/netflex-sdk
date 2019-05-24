<?php

use PHPUnit\Framework\TestCase;

final class GenerateHashTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once(__DIR__ . '/../../src/functions/common/functions_system.php');
  }

  public function testGenerateHash(): void
  {
    $password = '12345678';
    $this->assertNotEquals(
      $password,
      generate_hash($password)
    );
  }
}
