<?php

use PHPUnit\Framework\TestCase;

final class Common_ConvertToSafeStringTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_convert.php');
  }

  public function testHandlesText(): void
  {
    $this->assertEquals(
      convert_to_safe_string('This is a <strong>test</strong>', 'text'),
      "'This is a test'"
    );
  }

  public function testHandlesStr(): void
  {
    $this->assertEquals(
      convert_to_safe_string('This is a <strong>test</strong>', 'str'),
      'This is a test'
    );
  }

  public function testHandlesLong(): void
  {
    $this->assertEquals(
      convert_to_safe_string(1337, 'long'),
      1337
    );
  }

  public function testHandlesInt(): void
  {
    $this->assertEquals(
      convert_to_safe_string(1337, 'int'),
      1337
    );
  }

  public function testHandlesDouble(): void
  {
    $this->assertEquals(
      convert_to_safe_string('13.37', 'double'),
      13.37
    );

    $this->assertEquals(
      convert_to_safe_string('', 'double'),
      'null'
    );
  }

  public function testHandlesDate(): void
  {
    $this->assertEquals(
      convert_to_safe_string('2019-01-01 00:00:00', 'date'),
      "'2019-01-01 00:00:00'"
    );

    $this->assertEquals(
      convert_to_safe_string('', 'date'),
      'null'
    );
  }

  public function testHandlesDefined(): void
  {
    $this->assertEquals(
      convert_to_safe_string('test', 'defined', true, false),
      true
    );

    $this->assertEquals(
      convert_to_safe_string('', 'defined', true, false),
      false
    );
  }

  public function testHandlesAny(): void
  {
    $this->assertEquals(
      convert_to_safe_string('test', 'invalid'),
      'test'
    );

    $this->assertEquals(
      convert_to_safe_string(1337, 'invalid'),
      1337
    );

  }
}
