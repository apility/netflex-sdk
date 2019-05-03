<?php

namespace Netflex\Site;

use NF;

class Security
{

  const TOKEN_NAME = '__NF_SITE_CSRF_TOKEN__';

  public function __constructor()
  {
    if (!isset($_SESSION)) {
      session_start();
    }
  }

  /**
   * Creates a new CSRF token
   *
   * @return string
   */
  public function getToken()
  {
    $strong = true;
    $_SESSION[self::TOKEN_NAME] = bin2hex(openssl_random_pseudo_bytes(32, $strong));
    return $_SESSION[self::TOKEN_NAME];
  }

  /**
   * Verifies the given token
   *
   * @param string $token The token to verify
   * @return boolean
   */
  public function verifyToken($token)
  {
    $result = false;
    if (isset($_SESSION[self::TOKEN_NAME])) {
      $result = ($token === $_SESSION[self::TOKEN_NAME]);
      unset($_SESSION[self::TOKEN_NAME]); //We never want to re-use a token
    }
    return $result;
  }

  /**
   * Creates a cryptographically secure GUIDv4 string
   *
   * @return string
   */
  public function getGUID()
  {
    $strong = true;
    $data = openssl_random_pseudo_bytes(16, $strong);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }

  /**
   * Creates a 128-bit cryptographically secure hex string
   *
   * @return string
   */
  public function getSecureString()
  {
    $strong = true;
    $data = openssl_random_pseudo_bytes(32, $strong);
    return bin2hex($data);
  }
}
