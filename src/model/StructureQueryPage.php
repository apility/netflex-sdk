<?php

namespace Netflex;

use ArrayAccess;

trait DeprecatedFieldsInStructureQueryPage
{
  /**
   * @deprecated
   * @var int
   */
  public $last_page;

  /**
   * @deprecated
   * @var int|null
   */
  public $next_page;

  /**
   * @deprecated
   * @var int
   */
  public $total_items;

  /**
   * @deprecated
   * @var int|null
   */
  public $items_per_page;
}

/**
 * Class StructureQueryPage
 * @package Netflex
 * @mixin DeprecatedFieldsInStructureQueryPage
 */
class StructureQueryPage implements ArrayAccess
{
  public $page;
  public $items;
  public $size;
  public $currentPageSize;
  public $lastPage;
  public $previousPage;
  public $nextPage;
  public $totalItems;
  public $itemsPerPage;

  public function __get ($key)
  {
    $deprecatedFields = collect([
      'last_page' => 'lastPage',
      'next_page' => 'nextPage',
      'total_items' => 'totalItems',
      'items_per_page' => 'itemsPerPage',
    ]);

    $class = static::class;


    if ($deprecatedFields->has($key)) {
      trigger_error("Deprecated property: {$class}::\${$key}", E_USER_DEPRECATED);

      return $this->{$deprecatedFields->get($key)};
    }

    trigger_error("Undefined property: {$class}::\${$key}", E_USER_NOTICE);
  }

  public function __construct($items, $page, $size, $firstPageSize, $currentPageSize, $totalItems)
  {
    $this->size = $size;
    $this->currentPageSize = $currentPageSize;
    $this->totalItems = $totalItems;

    $pageSizeDifference = $firstPageSize - $size;

    $this->itemsPerPage = $pageSizeDifference !== 0
      ? null
      : $size;

    $this->lastPage = 1 + (int) ceil(($totalItems - $firstPageSize) / $size);

    $this->previousPage = $page === 0
      ? null
      : $page;

    $this->nextPage = $page === $this->lastPage - 2
      ? null
      : $page + 2;

    $this->items = $items;
    $this->page = $page + 1;
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
