<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class Common_GetGroupMembers extends TestCase
{
  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_convert.php');
    require_once('src/functions/common/functions_customers.php');
  }

  public function testCanRetrieveGroupMembers(): void
  {
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode(
      [
        [
          'id' => 1,
          'firstname' => 'Foo',
          'surname' => 'Bar',
          'groups' => '1',
          'mail' => 'foo@netflex.dev'
        ],
        [
          'id' => 2,
          'firstname' => 'Foo',
          'surname' => 'Baz',
          'groups' => '1,2',
          'mail' => 'baz@netflex.dev'
        ],
      ]
    )));

    $members = get_group_members(1);
    $this->assertNotEmpty($members);
    $this->assertIsArray($members);
    $this->assertArrayHasKey(0, $members);
    $this->assertArrayHasKey(1, $members);
  }

  public function testThrowsExceptionWhenNotFound(): void
  {
    NF::$capi->mockResponse(new Response(500));

    $this->expectException('GuzzleHttp\Exception\ServerException');
    get_group_members(1);
  }
}
