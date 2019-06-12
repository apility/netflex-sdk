<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Netflex\Site\Support\CaptchaV2;

final class CaptchaV2_IsValidTest extends TestCase
{
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

  public function testThrowsExceptionWhenParametersMissing (): void {
    $this->expectException('Netflex\Site\Support\ResponseMissingException');
    CaptchaV2::isValid();
  }

  public function testHandlesRequestException (): void {
    $_REQUEST['g-recaptcha-response'] = 'test';
    NF::$capi->mockResponse(new Response(500));
    NF::$site->mockVariable('captcha_site_key', 'aabbccddeeff');
    NF::$site->mockVariable('captcha_site_secret', '112233445566');
    $this->expectException('GuzzleHttp\Exception\ServerException');
    CaptchaV2::isValid();
  }

  public function testHandlesValidCase (): void {
    $_REQUEST['g-recaptcha-response'] = 'test';
    NF::$site->mockVariable('captcha_site_key', 'aabbccddeeff');
    NF::$site->mockVariable('captcha_site_secret', '112233445566');
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'success' => false,
      'error-codes' => [
        'Task failed successfully'
      ]
    ])));

    $this->expectException('Netflex\Site\Support\GoogleResponseException');
    CaptchaV2::isValid();
  }

  public function testHandlesInvalidCase (): void {
    $_REQUEST['g-recaptcha-response'] = 'test';
    NF::$site->mockVariable('captcha_site_key', 'aabbccddeeff');
    NF::$site->mockVariable('captcha_site_secret', '112233445566');
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'success' => true
    ])));

    $this->assertTrue(CaptchaV2::isValid());
  }
}
