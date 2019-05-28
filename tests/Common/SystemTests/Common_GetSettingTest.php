<?php

use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetSettingTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_system.php');
  }

  public function testGetSetting(): void
  {
    $key = 'test_setting';
    $value = 'test_value';
    NF::$site->mockVariable($key, $value);

    $this->assertMatchesSnapshot(get_setting($key), new TextDriver);
  }

  public function testGetSettingInvalid(): void
  {
    $this->assertMatchesSnapshot(get_setting('test_invalid_setting'), new TextDriver);
  }
}
