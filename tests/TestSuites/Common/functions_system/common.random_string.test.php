<?php

use PHPUnit\Framework\TestCase;

final class Common_RandomStringTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_system.php');
  }

  public function testRandomStringLength(): void
  {
    $this->assertEquals(
      0,
      strlen(random_string(0))
    );

    $this->assertEquals(
      1,
      strlen(random_string(1))
    );

    $this->assertEquals(
      10,
      strlen(random_string(10))
    );

    $this->assertEquals(
      62,
      strlen(random_string(62))
    );

    // Yeah, this one is weird
    $this->assertNotEquals(
      100,
      strlen(random_string(100))
    );
  }

  public function testRandomStringPattern(): void
  {
    $string = random_string(10);
    $this->assertRegExp('/[abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789]/', $string);
  }
}
