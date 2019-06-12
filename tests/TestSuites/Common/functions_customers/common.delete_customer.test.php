<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Common_DeleteCustomer extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_convert.php');
    require_once('src/functions/common/functions_customers.php');
  }

  public function testReturnsExpectedValue(): void
  {
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'id' => 1,
      'firstname' => 'Foo',
      'surname' => 'Bar',
      'mail' => 'foo@netflex.dev'
    ])));

    NF::$capi->mockResponse(new Response(200));

    $this->assertTrue(delete_customer('$1234567890abcdef'));
  }

  public function testThrowsExceptionWhenNotFound(): void
  {
    NF::$capi->mockResponse(new Response(404));
    $this->expectException('GuzzleHttp\Exception\ClientException');
    delete_customer('$1234567890abcdef');

    NF::$capi->mockResponse(new Response(404));
    $this->expectException('GuzzleHttp\Exception\ClientException');
    delete_customer(null);
  }
}
