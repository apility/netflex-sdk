<?php

use Netflex\Site\ElasticSearch;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\Snapshots\Drivers\JsonDriver;

final class ElasticSearchRelationTest extends TestCase
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
    $search->relation(ElasticSearch::PAGE);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search->relation(ElasticSearch::ENTRY);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search->relation(ElasticSearch::ORDER);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search->relation(ElasticSearch::SIGNUP);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search->relation(ElasticSearch::CUSTOMER);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );
  }
}
