<?php

use PHPUnit\Framework\TestCase;
use Netflex\Site\Support\CaptchaV2;
use Spatie\Snapshots\MatchesSnapshots;

final class CaptchaV2_ScriptTagTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/Netflex/Site/Support/CaptchaV2.php');
    require_once('src/functions/common/functions_system.php');
    require_once('src/Netflex/Site/Support/GoogleResponseException.php');
    require_once('src/Netflex/Site/Support/ResponseMissingException.php');
    require_once('src/Netflex/Site/Support/ConfigurationMissingException.php');
  }

  public function testMatchesSnapshots (): void {
    $this->assertMatchesSnapshot(CaptchaV2::scriptTag(), new TextDriver);
    $this->assertMatchesSnapshot(CaptchaV2::scriptTag(true), new TextDriver);
  }
}
