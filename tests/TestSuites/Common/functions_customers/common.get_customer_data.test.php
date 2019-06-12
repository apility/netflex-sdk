<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Common_GetCustomerData extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_convert.php');
    require_once('src/functions/common/functions_customers.php');
  }

  public function testCanRetrieveCustomer(): void
  {
    $mail = 'foo@netflex.dev';
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'id' => 1,
      'firstname' => 'Foo',
      'surname' => 'Bar',
      'mail' => 'foo@netflex.dev'
    ])));

    $customer = get_customer_data($mail);
    $this->assertNotEmpty($customer);
    $this->assertIsArray($customer);
    $this->assertArrayHasKey('mail', $customer);
    $this->assertEquals($mail, $customer['mail']);
  }

  public function testCanRetrieveCustomerField(): void
  {
    $mail = 'foo@netflex.dev';
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'id' => 1,
      'firstname' => 'Foo',
      'surname' => 'Bar',
      'mail' => 'foo@netflex.dev'
    ])));

    $firstname = get_customer_data($mail, 'firstname');
    $this->assertNotEmpty($firstname);
    $this->assertIsString($firstname);
    $this->assertEquals('Foo', $firstname);
  }

  public function testHandlesNotFound(): void
  {
    $mail = 'foo@netflex.dev';
    NF::$capi->mockResponse(new Response(404));

    $customer = get_customer_data($mail);
    $this->assertEmpty($customer);
  }
}
