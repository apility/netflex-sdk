<?php

namespace Netflex\Site;

use NF;
use Exception;

/**
 * A Search wrapper for the Netflex ElasticSearch API
 */
class ElasticSearch
{

  private $_result = null;
  private $_sort = [];
  private $_index = 'entry';
  private $_terms = [];

  /** @var bool */
  private $isRawSearch = false;

  private $query = [
    'index' => 'entry',
    '_source' => [],
    'body' => [
      'sort' => [],
      'query' => [
        'bool' => []
      ]
    ]
  ];

  /** Defines legal relation types */
  protected $_relations = [
    'page',
    'entry',
    'order',
    'signup',
    'customer',
  ];

  public function query($string = '', $or = false)
  {
    if (!isset($this->_terms['query'])) {
      $this->_terms['query'] = [];
    }

    $this->_terms['query'][] = [
      'key' => 'query',
      'param' => $string,
      'match' => 'must',
      'type' => 'query_string',
      'or' => $or
    ];

    return $this;
  }

  /**
   * Sets the relation for Search
   *
   * @params string $relation
   * @return Search
   */
  public function relation($relation)
  {
    $this->_query['index'] = $relation;
    return $this;
  }

  /**
   * Adds a directory to the search query
   *
   * @param string|int $directory
   * @return Search
   */
  public function directory($directory = null)
  {
    $this->_query['index'] = 'entry_' . $directory;
    $this->equals('directory_id', $directory);
    return $this;
  }

  /**
   * Excludes a directory from the search query
   *
   * @param string|int $directory
   * @return Search
   */
  public function notDirectory($directory = null)
  {
    $this->_query['index'] = 'entry';
    $this->notEquals('directory_id', $directory);
    return $this;
  }

  /**
   * Overrides the query string
   *
   * @params string $query Raw qery string
   * @return Search
   */
  public function raw($query)
  {
    $this->query = $query;
    $this->isRawSearch = true;
    return $this;
  }

  private function buildQuery()
  {
    if ($this->isRawSearch) {
      return $this->query;
    }

    $query = [];
    $previousTerm = null;
    $previousNode = null;
    foreach ($this->_terms as $field => $terms) {
      $terms = count($terms) ? $terms : [$terms];

      foreach ($terms as $term) {
        $queryNode = null;

        if ($term['or'] && (!$previousTerm || !$previousNode)) {
          throw new Exception('Invalid OR clause in query');
        }

        if (!isset($query[$term['match']])) {
          $query[$term['match']] = [];
        }

        if ($term['key'] === 'query') {
          $queryNode = ['query_string' => ['query' => $term['param']]];
        } else if (isset($term['param']['must_not'])) {
          $queryNode = [$term['type'] => $term['param']];
        } else {
          $queryNode = [$term['type'] => [$term['key'] => $term['param']]];
        }

        if ($term['or']) {
          if ($previousTerm['key'] === 'query') {
            array_pop($query[$previousTerm['match']]['query_string']);
            if (!count($query[$previousTerm['match']]['query_string'])) {
              unset($query[$previousTerm['match']]['query_string']);
            }
          } else if (isset($previousTerm['param']['must_not'])) {
            array_pop($query[$previousTerm['match']][$previous['type']]);
            if (!count($query[$previousTerm['match']][$previous['type']])) {
              unset($query[$previousTerm['match']][$previous['type']]);
            }
          } else {
            array_pop($query[$previousTerm['match']]);
            if (!count($query[$previousTerm['match']])) {
              unset($query[$previousTerm['match']]);
            }
          }
          $queryNode = ['bool' => ['should' => [$previousNode, $queryNode]]];
        }

        $previousTerm = $term;
        $previousNode = $queryNode;

        $query[$term['match']][] = $queryNode;
      }
    }

    $this->_query['body']['query'] = ['bool' => $query];
  }

  /**
   * Performs the actual search with the built query
   *
   * @params bool $fetch Fetches the search results
   * @params bool $debug Returns debug info for the search
   * @params array $order Sort key and order of the search
   * @params string $limit Limit results on search
   * @params string $from From result item for pagination
   * @params bool $count Returns the number of hits for the search
   * @params bool $json Returns the results as an Object
   * @return mixed
   */
  private function execute($debug = false)
  {
    $this->buildQuery();

    NF::debug(json_encode($this->_query, JSON_PRETTY_PRINT), 'ElasticSearchQuery');

    $url = 'search/raw';
    try {
      $result = NF::$capi
        ->post($url, ['json' => $this->_query])
        ->getBody();
      $this->_result = json_decode(
        str_replace('##D##', '-', json_encode(json_decode($result)))
      );
    } catch (Exception $ex) {
      $this->_result = json_decode(
        json_encode(['hits' => ['total' => 0]])
      );
      NF::$console->debug($ex);
      throw new Exception(json_encode($this->_query));
    }
  }

  /**
   * Builds a partial query string
   *
   * @params string $key The property to query
   * @params string $param The value to query
   * @params string $type The bool type type
   * @return string
   */
  public function where($key, $param, $type = 'term', $match = 'must', $or = false)
  {
    $key = str_replace('-', '##D##', $key);
    if (is_null($param)) {
      $type = 'bool';
      /* $type = 'filter'; */
      $param = [$match === 'must_not' ? 'must' : 'must_not' => ['exists' => ['field' => $key]]];
    }
    $this->_terms[$key] = !isset($this->_terms[$key]) ? [] : $this->_terms[$key];
    $this->_terms[$key][] = [
      'key' => $key,
      'param' => $param,
      'type' => $type,
      'match' => $match,
      'or' => $or
    ];

    return $this;
  }

  /**
   * Search where key not contains the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function notContains($key, $param, $or = false)
  {
    $this->where($key, $param, 'match', 'must_not', $or);
    return $this;
  }

  /**
   * Search where key contains the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function contains($key, $param, $or = false)
  {
    return $this->where($key, $param, 'match', 'must', $or);
  }

  /**
   * Search where key equals the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function equals($key, $param, $or = false)
  {
    return $this->where($key, $param, 'term', 'must', $or);
  }

  /**
   * Search where key not equals the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function notEquals($key, $param, $or = false)
  {
    return $this->where($key, $param, 'term', 'must_not', $or);
  }

  /**
   * Search where key is less than the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function lessThan($key, $param, $or = false)
  {
    $this->where($key, ['lt' => $param], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Search where key is less than the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function lessThanOrEqual($key, $param, $or = false)
  {
    $this->where($key, ['lte' => $param], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Search where key in range of $from -> $to value
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function range($key, $from, $to, $or = false)
  {
    $this->where($key, ['gte' => $from, 'lte' => $to], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Search where key is greater than the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function greaterThan($key, $param, $or = false)
  {
    $this->where($key, ['gt' => $param], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Search where key is greater than the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function greaterThanOrEqual($key, $param, $or = false)
  {
    $this->where($key, ['gte' => $param], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Number of items to return
   *
   * @params string $limit
   * @return Search
   */
  public function limit($limit)
  {
    $this->_query['size'] = $limit;
    return $this;
  }

  /**
   * Return records from this item for pagination
   *
   * @params string $from
   * @return Search
   */
  public function from($from)
  {
    $this->_query['from'] = $from;
    return $this;
  }

  public function paginate($from = 1, $size = 10)
  {
    $this->from($from);
    $this->limit($size);
    return $this;
  }

  /**
   * Returns the search results
   *
   * @return mixed
   */
  public function fetch()
  {
    $this->execute();
    return array_map(function ($hit) {
      return $hit->_source;
    }, $this->_result->hits->hits);
  }

  /**
   * Returns the raw search results
   *
   * @return mixed
   */
  public function getRawResults()
  {
    return $this->_result;
  }

  /**
   * Returns the ES debug info
   *
   * @return mixed
   */
  public function debug()
  {
    $this->buildQuery();
    return $this->_query;
  }

  /**
   * Specifies a field to get
   *
   * @params string $item Field name
   * @return Search
   */
  public function field($item)
  {
    $this->_query['_source'][] = $item;
    return $this;
  }

  /**
   * Specifies multiple fields to get
   *
   * @params array $items Array of field names
   * @return Search
   */
  public function fields($items = [])
  {
    foreach ($items as $item) {
      $this->field($item);
    }
    return $this;
  }

  /**
   * Sorts the search by the given key and direction
   *
   * @params string $key Field name
   * @params string $dir Direction
   * @return Search
   */
  public function sortBy($key = 'updated', $dir = 'asc')
  {
    $dir = !in_array($dir, ['asc', 'desc']) ? 'asc' : $dir;
    $this->_query['body']['sort'][] = [$key => $dir];
    return $this;
  }

  /**
   * Returns the number of found entries
   *
   * @return int
   */
  public function count()
  {
    $_source = $this->_query['_source'];
    $this->_query['_source'] = ['id'];
    $this->execute();
    $this->_query['_source'] = $_source;
    return $this->_result->hits->total;
  }
}
