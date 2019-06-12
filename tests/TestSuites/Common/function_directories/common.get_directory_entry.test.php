<?php

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

final class Common_GetDirectoryEntryTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/functions/common/functions_directories.php');
    require_once('src/functions/common/functions_convert.php');
  }

  public function testCanRetrieveFromCache (): void
  {
    NF::$cache->mockItem('entry/10000', [
      'id' => 10000,
      'name' => 'Test 1',
      'url' => 'test-1/',
      'revision' => 10000,
      'published' => true
    ]);

    $this->assertMatchesJsonSnapshot(get_directory_entry(10000));
  }

  public function testCanFetchFromApi (): void
  {
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'id' => 10001,
      'name' => 'Test 2',
      'url' => 'test-2/',
      'revision' => 10000,
      'published' => true
    ])));

    $this->assertMatchesJsonSnapshot(get_directory_entry(10001));
  }

  public function testHandlesNotFound (): void
  {
    NF::$capi->mockResponse(new Response(404));
    $this->assertEmpty(get_directory_entry(10002));
  }

  public function testCanOverrideRevision (): void
  {
    global $revision_override;
    global $entry_override;

    NF::$cache->mockItem('entry/10003', [
      'id' => 10003,
      'name' => 'Test 3',
      'url' => 'test-3/',
      'revision' => 10000,
      'published' => true
    ]);

    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'id' => 10003,
      'name' => 'Test 3',
      'url' => 'test-3/',
      'revision' => 10001,
      'published' => false
    ])));

    $entry_override = 10003;
    $revision_override = 10001;

    $this->assertMatchesJsonSnapshot(get_directory_entry(10003));

    NF::$cache->mockItem('entry/10098', [
      'id' => 10098,
      'name' => 'Test 3',
      'url' => 'test-3/',
      'revision' => 10002,
      'published' => true
    ]);

    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'id' => 10099,
      'name' => 'Test 3',
      'url' => 'test-3/',
      'revision' => 10003,
      'published' => false
    ])));

    $entry_override = 10099;
    $revision_override = 10003;

    $this->assertMatchesJsonSnapshot(get_directory_entry(10099));
    $this->assertMatchesJsonSnapshot(get_directory_entry(10098));
  }

  public function testRespectsPublishedAttribute (): void
  {
    NF::$capi->mockResponse(new Response(200, ['Content-Type' => 'application/json'], json_encode([
      'id' => 10004,
      'name' => 'Test 4',
      'url' => 'test-4/',
      'revision' => 10000,
      'published' => false
    ])));

    $this->assertNull(get_directory_entry(10004));
  }
}

