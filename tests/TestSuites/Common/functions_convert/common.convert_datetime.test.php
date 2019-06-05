<?php

use PHPUnit\Framework\TestCase;

final class Common_ConvertDatetimeTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_convert.php');
  }

  public function testHandlesNull(): void
  {
    $this->assertEquals(
      convert_datetime(null, 'Y/M/d'),
      null
    );

    $this->assertEquals(
      convert_datetime(0, 'Y/M/d'),
      null
    );

    $this->assertEquals(
      convert_datetime('', 'Y/M/d'),
      null
    );
  }

  public function testOutputsCorrectFormat(): void
  {
    $this->assertEquals(
      convert_datetime('2019-01-01 12:34:56', 'Y_d/M h-i-s'),
      '2019_01/Jan 12-34-56'
    );
  }
}
