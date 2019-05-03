<?php

namespace Netflex\Site;

use Exception;

class JWT
{
  private $secret;

  public function __construct($secret)
  {
    $this->secret = $secret;

    if (!$this->secret) {
      throw new Exception('JWT secret missing');
    }
  }

  private function base64UrlEncode($data)
  {
    $urlSafeData = strtr(base64_encode($data), '+/', '-_');
    return rtrim($urlSafeData, '=');
  }

  private function base64UrlDecode($data)
  {
    $urlUnsafeData = strtr($data, '-_', '+/');
    $paddedData = str_pad($urlUnsafeData, strlen($data) % 4, '=', STR_PAD_RIGHT);

    return base64_decode($paddedData);
  }


  public function create($payload = [])
  {
    $secret = $this->secret;

    if (!$secret) {
      throw new Exception('JWT secret missing');
    }

    $header = json_encode([
      'iat' => time(),
      'exp' => time() + 60,
      'iss' => 'netflex',
      'typ' => 'JWT',
      'alg' => 'HS256'
    ]);

    $payload['uid'] = (intval($_SESSION['user_id'] ?? 0));

    $payload = json_encode($payload);

    $base64UrlHeader = self::base64UrlEncode($header);
    $base64UrlPayload = self::base64UrlEncode($payload);

    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, get_setting('netflex_api'), true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
  }

  public function verify($jwt)
  {
    $secret = $this->secret;

    if (!$secret) {
      throw new Exception('JWT secret missing');
    }

    list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt);
    $dataEncoded = $headerEncoded . '.' . $payloadEncoded;
    $signature = self::base64UrlDecode($signatureEncoded);
    $rawSignature = hash_hmac('sha256', $dataEncoded, $secret, true);

    $payloadDecoded = (json_decode(self::base64UrlDecode($payloadEncoded)));

    return hash_equals($rawSignature, $signature) && $payloadDecoded->exp >= time();
  }

  public function decode($jwt)
  {
    list($_, $payloadEncoded) = array_map('self::base64UrlDecode', explode('.', $jwt));

    return json_decode($payloadEncoded);
  }
}
