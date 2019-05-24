<?php

use PHPUnit\Framework\TestCase;

final class GetLabelTest extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once(__DIR__ . '/../mocks/NF.php');
    require_once(__DIR__ . '/../../src/functions/common/functions_system.php');
  }

  public function testGetLabel(): void
  {
    NF::$site->mockLabel('Hello World!', 'Hei verden!', 'nb');
    $this->assertEquals(
      'Hei verden!',
      get_label('Hello World!', 'nb')
    );
  }

  public function testHandlesFallback(): void
  {
    $this->assertEquals(
      'Hello World!',
      get_label('Hello World!', 'en')
    );
  }
}
