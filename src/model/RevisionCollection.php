<?php

namespace Netflex;

use ArrayAccess;

/**
 * ReivisionCollection is a lazy loading revision handling class for Netflex\Structures
 *
 * It queries the server for a list of revisions and gives you access to the all revision
 * as an array.
 *
 * @see Netflex\Structure
 */
class RevisionCollection implements ArrayAccess
{

  /**
   * The class that new objects will be instanciated as
   * @var \Netflex\Structure
   */
  private $class = null;

  /**
   * Entry ID that we want to get revisions for
   * @var integer
   */
  private $objectId = 0;

  /**
   * Results
   * @var array
   */
  private $results = [];

  function __construct($class, $objectId)
  {
    $this->class    = $class;
    $this->objectId = $objectId;

    $data = json_decode(\NF::$capi->get("builder/structures/entry/" . $this->objectId . "/revisions/")->getBody(), true);
    foreach ($data as $revision) {
      $this->results[intval($revision['revision'])] = $revision;
    }
  }

  /**
   * Checks presence of object in array
   * @param  int                $offset Key in array
   * @return \Netflex\Structure Correct object or null
   */
  public function offsetExists($offset)
  {
    return array_key_exists($offset, $this->results);
  }

  /**
   * Gets a revision
   * @param  int                $offset Key in array
   * @return \Netflex\Structure null if does not exist
   */
  public function offsetGet($offset)
  {
    if (!$this->offsetExists($offset)) {
      return null;
    }

    if (is_array($this->results[$offset])) {
      $data = json_decode(\NF::$capi->get('builder/structures/entry/' . $this->objectId . '/revision/' . $this->results[$offset]['revision'])->getBody(), true);
      $this->results[$offset] = new $this->class($data);
    }

    return $this->results[$offset];
  }

  /**
   * Disabled
   * @param  [type] $offset [description]
   * @param  [type] $value  [description]
   * @return [type]         [description]
   */
  public function offsetSet($offset, $value)
  { }

  /**
   * Disabled
   * @param  [type] $offset [description]
   * @return [type]         [description]
   */
  public function offsetUnset($offset)
  { }

  public function __get($key)
  {
    if ($key === 'list') {
      return array_keys($this->results);
    }
  }

  /**
   * Gets first revision
   * @return \Netflex\Structure First revision
   */
  public function first()
  {
    return $this->offsetGet(min(array_keys($this->results)));
  }

  /**
   * Gets last revision
   * @return \Netflex\Structure Last revision
   */
  public function last()
  {
    return $this->offsetGet(max(array_keys($this->results)));
  }
}
