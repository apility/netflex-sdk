<?php

namespace Netflex;

use ArrayAccess;

class StructureQueryPage implements ArrayAccess
{
  public $page;
  public $items;
  public $size;
  public $next_page;
  public $last_page;
  public $total_items;
  public $items_per_page;

  public function __construct($items, $page, $size, $totalItems)
  {
    $this->size = $size;
    $this->total_items = $totalItems;
    $this->last_page = round($this->total_items / $this->size) | 0;
    $this->items = $items;
    $this->page = $page + (($page >= $this->last_page) ? 0 : 1);
  }

  public function offsetExists($key)
  {
    return property_exists($this, $key);
  }

  public function offsetGet($key)
  {
    return $this->{$key};
  }

  public function offsetSet($key, $value)
  {
    return;
  }

  public function offsetUnset($key)
  {
    return;
  }
}
