<?php

namespace Netflex\Site;

use Exception;
use GuzzleHttp\Client;
use NF;

class PDF {

  private $options;

  public function __construct ($url, $options = []) {
    $this->url = $url;
    $this->options = $options;
    $this->client = new Client();
  }

  public function wait ($wait = 0) {
    $this->options['wait'] = intval($wait);
    return $this;
  }

  public function scale ($scale = 1) {
    $this->options['scale'] = floatval($scale);
    return $this;
  }

  public function marginTop ($margin = 0.5) {
    $this->options['marginTop'] = floatval($margin);
    return $this;
  }

  public function marginLeft ($margin = 0.5) {
    $this->options['marginLeft'] = floatval($margin);
    return $this;
  }

  public function marginRight ($margin = 0.5) {
    $this->options['marginRight'] = floatval($margin);
    return $this;
  }

  public function marginBottom ($margin = 0.5) {
    $this->options['marginBottom'] = floatval($margin);
    return $this;
  }

  public function margin (...$margins) {
    $positions = ['Top', 'Right', 'Bottom', 'Left'];

    if (count($margins)) {
      foreach ($margins as $index => $margin) {
        if ($index > 3) {
          break;
        }
        if ($index === 0 && count($margins) === 1) {
          foreach ($positions as $position) {
            $this->{'margin' . $position}($margin);
          }
        } else {
          $this->{'margin' . $positions[$index]}($margin);
        }
      }
    }
    return $this;
  }

  public function width ($width = 210) {
    $this->options['paperWidth'] = intval($width);
    return $this;
  }

  public function height ($height = 297) {
    $this->options['paperHeight'] = intval($height);
    return $this;
  }

  public function landscape ($landscape = false) {
    $this->options['landscape'] = boolval($landscape);
    return $this;
  }

  public function background ($background = true) {
    $this->options['printBackground'] = boolval($background);
    return $this;
  }

  public function preferCSSPageSize ($preference = true) {
    $this->options['preferCSSPageSize'] = boolval($preference);
    return $this;
  }

  public function header ($template = '<!--header-->') {
    $this->options['displayHeaderFooter'] = true;
    $this->options['headerTemplate'] = $template;
    return $this;
  }

  public function footer ($enabled = false, $template = '<!--header-->') {
    $this->options['displayHeaderFooter'] = true;
    $this->options['footerTemplate'] = $template;
    return $this;
  }

  public function ignoreInvalidRange ($ignore = true) {
    $this->options['ignoreInvalidPageRanges'] = boolval($ignore);
    return $this;
  }

  public function range (...$ranges) {

    if (count($ranges) === 2 && is_int($ranges[0])) {
      $ranges = [[$ranges[0], $ranges[1]]];
    }
    $this->options['pageRanges'] = implode(',', array_map(function ($range) {
      return implode('-', $range);
    }, $ranges));

    return $this;
  }

  public function getURL () {
    $response = json_decode(
      NF::$capi->post(
        'foundation/pdf', [
          'json' => [
            'url' => $this->url,
            'options' => $this->options
          ]
        ]
      )->getBody()
    );

    return $response->url;
  }

  public function generate () {
    $url = $this->getURL();
    ob_start();
    ob_clean();

    if ($url) {
      header('Content-Type: application/pdf');
      $pdf = $this->client->get($url)
        ->getBody()
        ->getContents();

      die($pdf);
    }

    http_response_code(500);
    die();
  }

  public static function make ($url, $options = []) {
    return new static($url, $options);
  }
}
