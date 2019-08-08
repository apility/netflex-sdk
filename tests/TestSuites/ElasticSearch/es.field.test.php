<?php

use Netflex\Site\ElasticSearch;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\Snapshots\Drivers\JsonDriver;

final class ElasticSearchFieldTest extends TestCase
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
    $search->field('field1');

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );

    $search = new ElasticSearch;
    $search->fields(['field1', 'field2', 'field3']);

    $this->assertMatchesSnapshot(
      $search->buildQuery(),
      new JsonDriver
    );
  }
}
