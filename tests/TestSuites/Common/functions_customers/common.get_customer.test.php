<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Common_GetCustomer extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_customers.php');
  }

  public function canRetrieveFromCache (): void
  {
    NF::$cache->mockItem('customer/' . 10000, [
      'id' => 10000,
      'firstname' => 'Foo',
      'surname' => 'Bar',
      'mail' => 'foo@netflex.dev'
    ]);

    $customer = get_customer(10000);
    $this->assertNotEmpty($customer);
    $this->assertIsArray($customer);
    $this->assertArrayHasKey('id', $customer);
    $this->assertArrayHasKey('mail', $customer);
    $this->assertEquals(10000, $customer['id']);
    $this->assertEquals('foo@netflex.dev', $customer['mail']);
  }

  public function canFetchCustomerFromApi (): void
  {
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'id' => 10001,
      'firstname' => 'Foo',
      'surname' => 'Baz',
      'mail' => 'baz@netflex.dev'
    ])));

    $customer = get_customer(10001);
    $this->assertNotEmpty($customer);
    $this->assertIsArray($customer);
    $this->assertArrayHasKey('id', $customer);
    $this->assertArrayHasKey('mail', $customer);
    $this->assertEquals(10001, $customer['id']);
    $this->assertEquals('baz@netflex.dev', $customer['mail']);
  }

  public function handlesEmptyId (): void
  {
    $this->assertEmpty(
      get_customer(null)
    );
  }

  public function testThrowsExceptionWhenNotFound(): void
  {
    NF::$capi->mockResponse(new Response(404));

    $this->expectException('GuzzleHttp\Exception\ClientException');
    get_customer(10002);
  }
}
