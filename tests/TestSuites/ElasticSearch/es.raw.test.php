<?php

use Netflex\Site\ElasticSearch;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\Snapshots\Drivers\JsonDriver;

final class ElasticSearchRawTest extends TestCase
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

    $query = [
      'index' => 'test',
      '_source' => ['id', 'field1', 'field2'],
      'body' => [
        'sort' => [],
        'query' => [
          'bool' => []
        ]
      ]
    ];

    $search->raw($query);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search->limit(10);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search->relation('test2');

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );
  }
}
