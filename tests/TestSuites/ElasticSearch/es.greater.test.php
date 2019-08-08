<?php

use Netflex\Site\ElasticSearch;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\Snapshots\Drivers\JsonDriver;

final class ElasticSearchGreaterThanTest extends TestCase
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
    $search->greaterThan('test', 10);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search = new ElasticSearch;
    $search->greaterThanOrEqual('test', 10);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );
  }

  public function testExpectMissingOrClauseException(): void
  {
    $this->expectException('Exception');
    $search = new ElasticSearch;
    $search->greaterThan('test', 10, true);
    $search->buildQuery();

    $this->expectException('Exception');
    $search = new ElasticSearch;
    $search->greaterThanOrEqual('test', 10, true);
    $search->buildQuery();
  }
}
