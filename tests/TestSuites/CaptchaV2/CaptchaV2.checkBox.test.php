<?php

use PHPUnit\Framework\TestCase;
use Netflex\Site\Support\CaptchaV2;
use Spatie\Snapshots\MatchesSnapshots;

final class CaptchaV2_CheckBoxTest extends TestCase
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

  public function testThrowsExceptionWhenConfiguationMissing (): void {
    $this->expectException('Netflex\Site\Support\ConfigurationMissingException');
    CaptchaV2::checkBox();
  }

  public function testMatchesSnapshot (): void {
    NF::$site->mockVariable('captcha_site_key', 'aabbccddeeff');
    NF::$site->mockVariable('captcha_site_secret', '112233445566');
    $this->assertMatchesSnapshot(CaptchaV2::checkBox(), new TextDriver);
  }
}
