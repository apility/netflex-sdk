<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

class MockGuzzle {
  /** @var MockHandler */
  private $mock = null;

  /** @var HandlerStack */
  private $handler = null;

  /** @var Client */
  private $client = null;

  public function __construct () {
    $this->mock = new MockHandler([]);
    $this->handler = HandlerStack::create($this->mock);
    $this->client = new Client(['handler' => $this->handler]);
  }

  public function mockResponse (Response $response) {
    $this->mock->append($response);
  }

  public function get (...$args) {
    return call_user_func_array([$this->client, 'get'], $args);
  }

  public function put (...$args) {
    return call_user_func_array([$this->client, 'put'], $args);
  }

  public function post (...$args) {
    return call_user_func_array([$this->client, 'post'], $args);
  }

  public function delete (...$args) {
    return call_user_func_array([$this->client, 'delete'], $args);
  }
}
