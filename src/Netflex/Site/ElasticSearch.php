<?php

namespace Netflex\Site;

use NF;
use Exception;

/**
 * A Search wrapper for the Netflex ElasticSearch API
 */
class ElasticSearch
{
  /** @var array */
  private $terms = [];

  /** @var array|null */
  private $result = null;

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

  /** @var string */
  const PAGE = 'page';

  /** @var string */
  const ENTRY = 'entry';

  /** @var string */
  const ORDER = 'order';

  /** @var string */
  const SIGNUP = 'signup';

  /** @var string */
  const CUSTOMER = 'customer';

  /**
   * Perform a Lucenene query
   *
   * @param string $string
   * @param bool $or
   * @return static
   */
  public function query($string = '', $or = false)
  {
    if (!isset($this->terms['query'])) {
      $this->terms['query'] = [];
    }

    $this->terms['query'][] = [
      'key' => 'query',
      'param' => $string,
      'match' => 'must',
      'type' => 'query_string',
      'or' => $or
    ];

    return $this;
  }

  /**
   * Sets the relation
   *
   * @param string $relation
   * @return static
   */
  public function relation($relation)
  {
    $this->query['index'] = $relation;
    return $this;
  }

  /**
   * Adds a directory constraint
   *
   * @param int $directory
   * @return static
   */
  public function directory($directory = null)
  {
    $this->query['index'] = 'entry_' . $directory;
    $this->equals('directory_id', $directory);
    return $this;
  }

  /**
   * Excludes a directory
   *
   * @param int $directory
   * @return static
   */
  public function notDirectory($directory = null)
  {
    $this->query['index'] = 'entry';
    $this->notEquals('directory_id', $directory);
    return $this;
  }

  /**
   * Overrides the query string
   *
   * @param string $query Raw qery string
   * @return static
   */
  public function raw($query)
  {
    $this->query = $query;
    $this->isRawSearch = true;
    return $this;
  }

  /**
   * Compiles the query
   *
   * @return array
   */
  public function buildQuery()
  {
    if ($this->isRawSearch) {
      return $this->query;
    }

    $query = [];
    $previousTerm = null;
    $previousNode = null;
    foreach ($this->terms as $field => $terms) {
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
            array_pop($query[$previousTerm['match']][$previousTerm['type']]);
            if (!count($query[$previousTerm['match']][$previousTerm['type']])) {
              unset($query[$previousTerm['match']][$previousTerm['type']]);
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

    $this->query['body']['query'] = ['bool' => $query];

    return $this->query;
  }

  /**
   * Performs the actual search with the built query
   *
   * @throws Exception
   * @return static
   */
  public function execute()
  {
    if (!get_setting('use_elasticsearch')) {
      throw new Exception('ElasticSearch is not enabled for this site');
    }

    $this->buildQuery();

    NF::debug(json_encode($this->query, JSON_PRETTY_PRINT), 'ElasticSearch');

    try {
      $result = NF::$capi->post('search/raw', [
        'json' => $this->query
      ])->getBody();

      $this->result = json_decode(
        str_replace('##D##', '-', json_encode(json_decode($result)))
      );
    } catch (Exception $ex) {
      $this->result = json_decode(
        json_encode(['hits' => ['total' => 0]])
      );

      NF::debug($ex, 'ElasticSearch');
      throw new Exception(json_encode($this->query));
    }
    
    return $this;
  }

  /**
   * Builds a partial query string
   *
   * @param string $key The property to query
   * @param string $param The value to query
   * @param string $type The bool type type
   * @param string $match
   * @param bool $or = false
   * @return static
   */
  public function where($key, $param, $type = 'term', $match = 'must', $or = false)
  {
    $key = str_replace('-', '##D##', $key);

    if (is_null($param)) {
      $type = 'bool';
      $match_type = $match === 'must_not'? 'must' : 'must_not';
      $param = [$match_type => ['exists' => ['field' => $key]]];
    }

    $this->terms[$key] = $this->terms[$key] ?? [];
    $this->terms[$key][] = [
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
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function notContains($key, $param, $or = false)
  {
    $this->where($key, $param, 'match', 'must_not', $or);
    return $this;
  }

  /**
   * Search where key contains the given param
   *
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function contains($key, $param, $or = false)
  {
    return $this->where($key, $param, 'match', 'must', $or);
  }

  /**
   * Search where key equals the given param
   *
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function equals($key, $param, $or = false)
  {
    return $this->where($key, $param, 'term', 'must', $or);
  }

  /**
   * Search where key not equals the given param
   *
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function notEquals($key, $param, $or = false)
  {
    return $this->where($key, $param, 'term', 'must_not', $or);
  }

  /**
   * Search where key is less than the given param
   *
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function lessThan($key, $param, $or = false)
  {
    $this->where($key, ['lt' => $param], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Search where key is less than the given param
   *
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function lessThanOrEqual($key, $param, $or = false)
  {
    $this->where($key, ['lte' => $param], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Search where key in range of $from -> $to value
   *
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function range($key, $from, $to, $or = false)
  {
    $this->where($key, ['gte' => $from, 'lte' => $to], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Search where key is greater than the given param
   *
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function greaterThan($key, $param, $or = false)
  {
    $this->where($key, ['gt' => $param], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Search where key is greater than the given param
   *
   * @param string $key
   * @param mixed $param
   * @param bool $or = false
   * @return static
   */
  public function greaterThanOrEqual($key, $param, $or = false)
  {
    $this->where($key, ['gte' => $param], $type = 'range', $match = 'must', $or);
    return $this;
  }

  /**
   * Max items to return
   *
   * @param string $limit
   * @return static
   */
  public function limit($limit)
  {
    $this->query['size'] = $limit;
    return $this;
  }

  /**
   * Return records from this item for pagination
   *
   * @param string $from
   * @return static
   */
  public function from($from)
  {
    $this->query['from'] = $from;
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
    }, $this->result->hits->hits);
  }

  /**
   * Returns the raw search results
   *
   * @return mixed
   */
  public function getRawResults()
  {
    return $this->result;
  }

  /**
   * Returns the ES debug info
   *
   * @return mixed
   */
  public function debug()
  {
    return $this->buildQuery();
  }

  /**
   * Specifies a field to get
   *
   * @param string $item Field name
   * @return static
   */
  public function field($item)
  {
    $this->query['_source'][] = $item;
    return $this;
  }

  /**
   * Specifies multiple fields to get
   *
   * @param array $items Array of field names
   * @return static
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
   * @param string $key Field name
   * @param string $dir Direction
   * @return static
   */
  public function sortBy($key = 'updated', $dir = 'asc')
  {
    $dir = !in_array($dir, ['asc', 'desc']) ? 'asc' : $dir;
    $this->query['body']['sort'][] = [$key => $dir];
    return $this;
  }

  /**
   * Returns the number of found entries
   *
   * @return int
   */
  public function count()
  {
    $_source = $this->query['_source'];
    $this->query['_source'] = ['id'];
    $this->execute();
    $this->query['_source'] = $_source;
    return $this->result->hits->total;
  }
}
