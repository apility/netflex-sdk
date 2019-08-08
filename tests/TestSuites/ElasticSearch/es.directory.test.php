<?php

use Netflex\Site\ElasticSearch;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\Snapshots\Drivers\JsonDriver;

final class ElasticSearchDirectoryTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/Netflex/Site/ElasticSearch.php');
  }

  public function testDirectoryOutputsMatchesSnapshot(): void
  {
    $search = new ElasticSearch;
    $search->directory(10000);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );
  }

  public function testNotOutputsMatchesSnapshot(): void
  {
    $search = new ElasticSearch;
    $search->notDirectory(10000);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );
  }
}
