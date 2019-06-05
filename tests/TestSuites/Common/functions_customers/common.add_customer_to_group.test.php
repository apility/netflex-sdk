<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Common_AddCustomerToGroup extends TestCase
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
    NF::$capi->mockResponse(new Response(200));
    $this->assertEquals(
      1,
      add_customer_to_group(10000, 4)
    );
  }

  public function testThrowsExceptionWhenNotFound(): void
  {
    NF::$capi->mockResponse(new Response(500));

    $this->expectException('GuzzleHttp\Exception\ServerException');
    add_customer_to_group(10000, 4);
  }
}
