<?php

namespace Netflex;

use NF;
use Exception;
use Carbon\Carbon;
use Netflex\Site\ElasticSearch;

class StructureQuery
{
  private $_class;
  private $_query;
  private $_directory;
  private $_current;
  private $_previous;
  private $_limit = -1;
  private $_sortBy = 'id';
  private $_sortDesc = false;
  private $_first = false;
  private $_paginate = false;
  private $_page = null;
  private $_from = null;
  private $_size = null;
  private $_currentPageSize = null;
  private $_firstPageSize = null;
  private $_fieldsModified = false;

  public function __construct($directory, $class)
  {
    $this->_class = $class;
    $this->_directory = $directory;
    $this->_search = new ElasticSearch();
    $this->_search->directory($this->_directory);
    $this->_search->limit(10000);
  }

  private function isArgsArray($args)
  {
    return count($args) === 1 && is_array($args[0]);
  }

  private function invoke($args, $method)
  {
    foreach ($args[0] as $arg) {
      call_user_func_array($method, $arg);
    }

    return $this;
  }

  private function parseArgs($args)
  {
    if (!count($args)) {
      return null;
    }

    $operator = count($args) === 2 ? '=' : $args[1];
    $value = isset($args[count($args) === 2 ? 1 : 2]) ? $args[count($args) === 2 ? 1 : 2] : null;

    return [
      'field' => $args[0],
      'operator' => count($args) === 2 ? '=' : $args[1],
      'value' => $value
    ];
  }

  private function mapMethod($operator)
  {
    switch (strtolower($operator)) {
      case '=':
      case 'equals':
        return 'equals';
      case '!=':
      case 'not equals':
        return 'notEquals';
      case 'like':
        return 'contains';
      case 'not like':
        return 'notContains';
      case '<':
        return 'lessThan';
      case '>':
        return 'greaterThan';
      case '<=':
        return 'greaterThanOrEqual';
      case '>=':
        return 'lessThanOrEqual';
    }
  }

  public function where(...$args)
  {
    if ($this->isArgsArray($args)) {
      return $this->invoke($args, [$this, 'where']);
    }

    $parsedArgs = $this->parseArgs($args);
    $field = $parsedArgs['field'];
    $operator = $parsedArgs['operator'];
    $value = $parsedArgs['value'];

    if (is_null($value)) {
      $type = $this->_class->getFieldType($field);
      switch ($type) {
        case 'checkbox':
          $value = false;
          break;
        case 'integer':
        case 'customer':
        case 'entry':
        case 'float':
          $value = 0;
          break;
        default:
          $value = null;
          break;
      }
    }

    $method = $this->mapMethod($operator);

    $this->_search->{$method}($field, $value, false);

    return $this;
  }

  public function orWhere(...$args)
  {
    if ($this->isArgsArray($args)) {
      return $this->invoke($args, [$this, 'orWhere']);
    }

    $parsedArgs = $this->parseArgs($args);
    $field = $parsedArgs['field'];
    $operator = $parsedArgs['operator'];
    $value = $parsedArgs['value'];
    $method = $this->mapMethod($operator);

    $this->_search->{$method}($field, $value, true);

    return $this;
  }

  public function whereBetween($field, $from, $to, $or = false)
  {
    if (in_array($this->_class->getFieldType($field), ['date', 'datetime'])) {
      $from = $from instanceof Carbon ? $from->toDateTimeString() : $from;
      $to = $to instanceof Carbon ? $to->toDateTimeString() : $to;
    }

    $this->_search->range($field, $from, $to, $or);

    return $this;
  }

  public function orWhereBetween($field, $from, $to)
  {
    return $this->whereBetween($field, $from, $to, true);
  }

  public function take($amount)
  {
    $this->_limit = $amount;
    $this->_search->limit($amount);

    return $this;
  }

  public function limit($amount)
  {
    return $this->take($amount);
  }

  public function orderBy($field, $dir = 'ASC')
  {
    $this->_sortBy = $field;
    $this->_sortDesc = strtolower($dir) === 'desc';

    $type = $this->_class->getFieldType($field);

    $rawTypes = ['text', 'textarea', 'string'];
    $rawFields = ['name', 'title', 'url', 'author'];

    if (in_array($type, $rawTypes) || in_array($field, $rawFields)) {
      $field = $field . ".raw";
    }

    $this->_search->sortBy($field, strtolower($dir));

    return $this;
  }

  public function first()
  {
    $this->limit(1);
    return $this->get()->first();
  }

  public function firstOrFail()
  {
    $entry = $this->first();

    if (!$entry) {
      throw new Exception('Entry not found');
    }

    return $entry;
  }

  public function pluck($field)
  {
    $this->_search->field($field);

    return $this->get()->pluck($field);
  }

  public function query($query, $or = false)
  {
    $query = 'directory_id:' . $this->_directory . ' AND (' . $query . ')';
    $this->_search->query($query, $or);

    return $this;
  }

  public function orQuery($query)
  {
    return $this->query($query, true);
  }

  public function debug()
  {
    return $this->_search->debug();
  }

  public function field(...$fields)
  {
    foreach ($fields as $field) {
      $this->_search->field($field);
    }

    $this->_fieldsModified = true;

    return $this;
  }

  public function fields($fields)
  {
    return $this->field($fields);
  }

  public function paginate($size, $page = 1, $firstPageSize = null)
  {
    $this->_paginate = true;

    $this->_page = max($page, 1) - 1;

    $this->_size = $size;

    $this->_firstPageSize = $firstPageSize ?? $this->_size;

    $this->_currentPageSize = $this->_page === 0
      ? $this->_firstPageSize
      : $this->_size;


    $this->_from = $this->_page === 0
      ? 0
      : $this->_firstPageSize + $this->_size * ($this->_page - 1);

    $this->_search->paginate($this->_from, $this->_currentPageSize);

    return $this->get();
  }

  public function count()
  {
    return $this->_search->count();
  }

  public function get()
  {
    if ($this->_limit === 0) {
      return collect([]);
    }

    $results = [];
    $this->_search->fetch();
    $results = $this->_search->getRawResults();
    $totalItems = $results->hits->total;

    $results = json_decode(json_encode(array_map(function ($hit) {
      return $hit->_source;
    }, $results->hits->hits)), true);

    $results = array_map(function ($entry) {
      $cacheKey = 'entry/' . $entry['id'];
      unset($entry->relation);
      unset($entry->relation_id);
      unset($entry->smart_score);
      unset($entry->total_score);

      if (NF::$cache->has($cacheKey)) {
        $data = NF::$cache->fetch($cacheKey);
        foreach ($entry as $key => $value) {
          $data[$key] = $value;
        }

        $entry = $data;
      }

      NF::$cache->save($cacheKey, $entry);

      return $this->_class::generateObject($entry);
    }, $results);

    $results = collect(array_values($results));

    if (!$this->_paginate) {
      return $results->values();
    }

    return new StructureQueryPage(
      $results,
      $this->_page,
      $this->_size,
      $this->_firstPageSize,
      $this->_currentPageSize,
      $totalItems
    );
  }
}
