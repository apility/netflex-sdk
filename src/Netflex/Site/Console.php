<?php

namespace Netflex\Site;

use PhpConsole\Handler;

class Console {
  /** @var Handler */
  private $handler;

  /** @var static */
  private static $instance;

  protected function __construct()
  {
    if (getenv('ENV') !== 'master') {
      $this->handler = Handler::getInstance();;
      $this->handler->getConnector()->setSourcesBasePath($_SERVER['DOCUMENT_ROOT']);
      $this->handler->start();
    }
  }

  /**
   * Instantiates a Console instance
   *
   * @return Console
   */
  public static function getInstance () {
    if (!self::$instance) {
      self::$instance = new static;
    }

    return self::$instance;
  }

  /**
   * Logs the text to console (if not in production)
   *
   * @param string $text
   * @param string $labels One or more labels separated by .
   * @return void
   */
  public function log ($text, $labels = null) {
    if ($this->handler) {
      $this->handler->debug($text, $labels);
    }
  }

  /**
   * @deprecated 1.0.11
   *
   * @param string $text
   * @param string $label
   * @return void
   */
  public function debug ($text, $label)
  {
    trigger_error('NF::$console->debug is deprecated', E_USER_DEPRECATED);
    return $this->log($text, $label);
  }
}
