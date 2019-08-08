<?php

use Netflex\Site\ElasticSearch;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\Snapshots\Drivers\JsonDriver;

final class ElasticSearchWhereTest extends TestCase
{
  use MatchesSnapshots;

  /**
   * Call this template method before each test method is run.
   */
  protected function setUp(): void
  {
    require_once('src/Netflex/Site/ElasticSearch.php');
  }

  public function testOutputsMatchesSnapshots(): void
  {
    $search = new ElasticSearch;
    $search->where('test', 'abc');

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search = new ElasticSearch;
    $search->where('test', null);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search = new ElasticSearch;
    $search->where('test', 'abc', 'cde', 'fgh');

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search = new ElasticSearch;
    $search->where('test', null, 'cde', 'fgh');

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );
  }
}
