<?php

namespace Netflex\Site;

use NF;
use Exception;

/**
 * A Search wrapper for the Netflex Search API
 */
class Search
{
  /** Defines legal relation types */
  protected $relations = [
    'page',
    'entry',
    'order',
    'signup',
    'customer',
  ];

  private $_from = 0;
  private $_limit = 0;
  private $_query = '';
  private $_json = true;
  private $_fields = [];
  private $_result = [];
  private $_orderBy = [];
  private $_fetch = false;
  private $_count = false;
  private $_debug = false;
  private $_relation = null;
  private $_directories = null;

  public function es($query = [], $relation = 'entry')
  {
    $query['index'] = $relation;
    $result = NF::$capi
      ->post(
        'elasticsearch/raw',
        ['json' => $query]
      )->getBody();

    return json_decode($result);
  }

  /**
   * Adds a directory to the search query
   *
   * @param string|int $directory
   * @return Search
   */
  public function directory($directory = null)
  {
    if (!is_numeric($directory)) {
      $directory = $this->mapDirectory($directory);
    }
    if ($directory) {
      $this->equals('directory_id', $directory);
    }
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
    if (!is_numeric($directory)) {
      $directory = $this->mapDirectory($directory);
    }
    if ($directory) {
      $this->notEquals('directory_id', $directory);
    }
    return $this;
  }

  /**
   * Maps a directory name to a id
   *
   * @param string $directoryName
   * @return int
   */
  private function mapDirectory($directoryName)
  {
    if (!$this->_directories) {
      $this->_directories = json_decode(NF::$capi->get('builder/structures')->getBody());
    }
    $found = null;
    foreach ($this->_directories as $directory) {
      if (strtolower($directory->name) === strtolower($directoryName)) {
        $found = intval($directory->id);
        break;
      }
    }
    return $found;
  }

  /**
   * Returns the current query string
   *
   * @params void
   * @return string The current query string
   */
  public function getQueryString()
  {
    return trim($this->_query);
  }

  /**
   * Returns the full post payload
   *
   * @params void
   * @return array The current post payload
   */
  public function buildQuery()
  {
    return [
      'terms' => $this->getQueryString(),
      'relation' => $this->_relation,
      'fetch' => $this->_fetch,
      'order' => $this->_orderBy,
      'debug' => $this->_debug,
      'limit' => $this->_limit,
      'from' => $this->_from,
      'count' => $this->_count,
      'fields' => $this->_fields ? $this->_fields : null
    ];
  }

  /**
   * Sets the relation for Search
   *
   * @params string $relation
   * @return Search
   */
  public function relation($relation)
  {
    if (!in_array($relation, $this->relations)) {
      throw new Exception('Invalid relation type: ' . $relation);
    }
    $this->_relation = $relation;
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
    $this->_query = $query;
    return $this;
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
  private function execute($fetch = false, $debug = false, $count = false, $json = true)
  {
    $url = 'search' . ($this->_relation ? '/' . $this->_relation : '');
    $result = NF::$capi
      ->post($url, ['json' => $this->buildQuery()])
      ->getBody();
    return $json ? json_decode($result) : $result;
  }

  /**
   * Builds a partial query string
   *
   * @params string $key The property to query
   * @params string $param The search query for the $key
   * @params string $type The query operation to perform
   * @return string
   */
  public function where($key, $param, $type = ':')
  {
    return $key . $type . $param . ' ';
  }

  /**
   * Search where key not contains the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function notContains($key, $param)
  {
    $this->_query .= '-' . $this->where($key, $param, ':');
    return $this;
  }

  /**
   * Search where key contains the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function contains($key, $param)
  {
    $this->_query .= $this->where($key, $param, ':');
    return $this;
  }

  /**
   * Search where key equals the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function equals($key, $param)
  {
    $this->_query .= $this->where($key, $param, '::');
    return $this;
  }

  /**
   * Search where key not equals the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function notEquals($key, $param)
  {
    $this->_query .= '-' . $this->where($key, $param, '::');
    return $this;
  }

  /**
   * Search where key is less than the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function lessThan($key, $param)
  {
    $this->_query .= $this->where($key, $param, ':<');
    return $this;
  }

  /**
   * Search where key is greater than the given param
   *
   * @params string $key
   * @params mixed $param
   * @return Search
   */
  public function greaterThan($key, $param)
  {
    $this->_query .= $this->where($key, $param, ':>');
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
    $this->_limit = $limit;
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
    $this->_from = $from;
    return $this;
  }

  /**
   * Removes unused fields from output
   *
   * @param array $fetched
   * @return array
   */
  private function formatOutput($fetched)
  {
    foreach ($fetched as $result) {
      foreach ($result as $key => $value) {
        if (!in_array($key, $this->_fields)) {
          unset($result->{$key});
        }
      }
    }
    return $fetched;
  }

  /**
   * Returns the search results
   *
   * @return mixed
   */
  public function fetch()
  {
    $this->_fetch = true;
    $fetched = $this->execute();
    if ($this->_fields) {
      $fetched = $this->formatOutput($fetched);
    }
    return $fetched;
  }

  /**
   * Returns the ES debug info
   *
   * @return mixed
   */
  public function debug()
  {
    $this->_debug = true;
    return $this->execute();
  }

  /**
   * Specifies a field to get
   *
   * @params string $item Field name
   * @return Search
   */
  public function field($item)
  {
    $this->_fields[] = $item;
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
   * @param int $sort Sort method
   * @return Search
   */
  public function sortBy($key = 'total_score', $dir = 'desc', $sort = SORT_STRING)
  {
    $this->_orderBy[$key] = [$sort, $dir == 'desc' ? SORT_DESC : SORT_ASC];
    return $this;
  }


  /**
   * Returns the search results meta data
   *
   * @return mixed
   */
  public function get()
  {
    return $this->execute();
  }

  /**
   * Returns the number of found entries
   *
   * @return int
   */
  public function count()
  {
    $this->_json = false;
    $this->_count = true;
    return intval($this->execute());
  }

  /**
   * Returns debug info
   *
   * @return array
   */
  public function __debugInfo()
  {
    return $this->buildQuery();
  }

  /**
   * Casts instance to string
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getQueryString();
  }
}
