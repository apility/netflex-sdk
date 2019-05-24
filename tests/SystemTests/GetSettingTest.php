<?php

use PHPUnit\Framework\TestCase;

final class GetSettingTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once(__DIR__ . '/../mocks/NF.php');
    require_once(__DIR__ . '/../../src/functions/common/functions_system.php');
  }

  public function testGetSetting(): void
  {
    $key = 'test_setting';
    $value = 'test_value';
    NF::$site->mockVariable($key, $value);

    $this->assertEquals(
      $value,
      get_setting($key)
    );

    $this->assertEquals(
      null,
      get_setting('test_invalid_setting')
    );
  }
}
