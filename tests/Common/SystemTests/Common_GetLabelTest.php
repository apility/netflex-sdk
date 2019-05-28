<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetLabelTest extends TestCase
{
  use MatchesSnapshots;
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_system.php');
  }

  public function testGetLabel(): void
  {
    NF::$site->mockLabel('Hello World!', 'Hei verden!', 'nb');
    $this->assertMatchesSnapshot(get_label('Hello World!', 'nb'), new TextDriver);
  }

  public function testHandlesFallback(): void
  {
    $this->assertMatchesSnapshot(get_label('Hello World!', 'en'), new TextDriver);
  }
}
