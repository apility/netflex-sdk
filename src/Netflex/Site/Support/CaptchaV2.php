<?php

namespace Netflex\Site\Support;

use NF;
use Netflex\Site\Support\ResponseMissingException;
use Netflex\Site\Support\GoogleResponseException;
use Netflex\Site\Support\ConfigurationMissingException;

/**
 * CaptchaV2 is an implementation of Google's reCaptcha V2.
 *
 * @see ./CaptchaV2.md Extended documentation for this class
 */
class CaptchaV2
{
  private static $printTag = false;
  /**
   * Gets the contents of the captcha config
   * @return Object Captcha Config
   */
  private static function getCredentials()
  {
    $siteKey = get_setting('captcha_site_key');
    if ($siteKey === null)
      throw new ConfigurationMissingException('captcha_site_key setting is missing');
    $siteSecret = get_setting('captcha_site_secret');
    if ($siteSecret === null)
      throw new ConfigurationMissingException('captcha_site_key setting is missing');

    return (object)[
      'siteKey'     => $siteKey,
      'siteSecret'  => $siteSecret
    ];
  }

  /**
   * Returns the script tag that has to be included for the captcha to work
   * @return string Script tag
   */
  public static function scriptTag($override = null)
  {
    return ($override ?? static::$printTag) ? '<script src="https://www.google.com/recaptcha/api.js"></script>' : '';
  }

  /**
   * Returns the actual checkbox object that should be injected into your form
   * @return string Checkbox tag
   */
  public static function checkBox()
  {
    static::$printTag = true;
    return '<div class="g-recaptcha" data-sitekey="' . static::getCredentials()->siteKey . '"></div>';
  }


  /**
   * Checks if the form that was submitted succeeds the Captcha Challenge
   * @throws \Netflex\Site\Support\ResponseMissingException If the g-recaptcha-response key is missing from the submitted form
   * @throws \Netflex\Site\Support\GoogleResponseException If the challenge fails for whatever reason
   * @return boolean true if challenge was successful
   */
  public static function isValid()
  {
    $response = null;

    if (array_key_exists('g-recaptcha-response', $_REQUEST)) {
      $response = $_REQUEST['g-recaptcha-response'];
    }

    if (is_null($response)) {
      throw new ResponseMissingException('g-recaptcha-response is missing from $_GET and $_POST');
    }

    $response = json_decode(NF::$capi->post('https://www.google.com/recaptcha/api/siteverify', [
      'form_params' => [
        'secret'    => static::getCredentials()->siteSecret,
        'response'  => $response,
        'remoteip'  => $_SERVER['REMOTE_ADDR']
      ]
    ])->getBody());

    if (isset($response->{'error-codes'})) {
      throw new GoogleResponseException($response->{'error-codes'}[0]);
    }
    return $response->success;
  }
}
