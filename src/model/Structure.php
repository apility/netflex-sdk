<?php

namespace Netflex;

global $previewmode;

use NF;
use Exception;
use Closure;
use ArrayAccess;
use Serializable;
use Carbon\Carbon;
use JsonSerializable;
use Illuminate\Support\Collection;

require_once(__DIR__ . '/FieldMapping.php');
require_once(__DIR__ . '/StructureQuery.php');
require_once(__DIR__ . '/RevisionCollection.php');

abstract class Structure implements ArrayAccess, Serializable, JsonSerializable
{

  use FieldMapping;

  protected static $_booted = false;
  protected static $_hooks = [];
  protected static $_existing_hooks = [
    'retrieved',
    'creating',
    'created',
    'updating',
    'updated',
    'saving',
    'saved',
    'deleting',
    'deleted'
  ];
  private $_client;

  protected $_modified = [];
  protected $attributes = [];
  protected $hidden = [];
  protected $directory = null;
  protected $typecasting = true;
  protected $hideDefaultFields = false;
  protected $dates = ['created', 'updated'];
  protected $mapFieldCodes = false;
  protected $_append = [];
  protected $_revisions = null;

  /**
   * [__construct description]
   * @param array   $attributes     [description]
   * @param boolean $skipInitialSet [description]
   */
  public function __construct($attributes = [])
  {
    if (is_string($attributes)) {
      $this->attributes['name'] = $attributes;
      $this->_modified[] = 'name';
    }

    if (!is_string($attributes) && (is_object($attributes) || is_array($attributes))) {
      $attributes = json_decode(json_encode($attributes));
      foreach ($attributes as $key => $value) {
        $this->attributes[$key] = $value;
        $this->_modified[] = $key;
      }
      if (in_array('id', $this->_modified)) {
        $this->_modified = json_decode("[]");
        static::performHookOn($this, "retrieved");
      }
    }

    static::bootUnlessBooted();
  }

  public function save()
  {
    static::performHookOn($this, 'saving');
    $payload = [
      'revision_publish' => true
    ];


    foreach ($this->_modified as $key) {
      $payload[$key] = $this->attributes[$key];
    }

    $payload = ['json' => $payload];

    if (count($this->_modified)) {
      if ($this->id) {
        static::performHookOn($this, 'updating');
        NF::$capi->put('builder/structures/entry/' . $this->id, $payload);
        static::performHookOn($this, 'updated');
      } else {
        static::performHookOn($this, 'creating');
        $response = NF::$capi->post('builder/structures/' . $this->directory . '/entry', $payload);
        static::performHookOn($this, 'created');
        $response = json_decode($response->getBody());
        $this->attributes['id'] = $response->entry_id;
        $response = NF::$capi->get('builder/structures/entry/' . $this->attributes['id']);
        $response = json_decode($response->getBody());
        foreach ($response as $key => $value) {
          $this->attributes[$key] = $value;
        }
      }
    }

    $this->_modified = [];
    NF::$cache->save('builder_structures_entry_' . $this->id, serialize($this->attributes));

    static::performHookOn($this, 'saved');
    return $this;
  }

  public function delete()
  {
    if (!$this->id) {
      throw new Exception('Unable to delete entry');
    }

    static::performHookOn($this, 'deleting');
    NF::$capi->delete('builder/structures/entry/' . $this->id);
    NF::$cache->delete('builder_structures_entry_' . $this->id);
    static::performHookOn($this, 'deleted');
    return $this;
  }

  public function toArray()
  {
    return $this->jsonSerialize();
  }

  public function __get($key)
  {
    $value = null;

    if ($key === "revisions") {
      if ($this->_revisions === null) {
        $this->_revisions = new RevisionCollection(static::class, $this->id);
      }
      $value = $this->_revisions;
    }

    if (array_key_exists($key, $this->attributes)) {
      $value = $this->attributes[$key];
    }

    $getter = str_replace('_', '', 'get' . $key . 'attribute');
    $value = $this->__typeCast($key, $value);

    if (method_exists($this, $getter)) {
      $value = $this->{$getter}($value);
    }

    if (in_array($key, $this->dates) && $this->typecasting) {
      return Carbon::parse($value);
    }

    return $value;
  }

  public function __set($key, $value)
  {
    if ($key === "revisions") {
      return;
    }
    if ($this->offsetExists($key)) {
      $setter = str_replace('_', '', 'set' . $key . 'attribute');

      if (method_exists($this, $setter)) {
        $value = $this->{$setter}($value);
      }

      if ($this->attributes[$key] !== $value) {
        $this->attributes[$key] = $value;
        if (!in_array($key, $this->_modified)) {
          $this->_modified[] = $key;
        }
      }
    }
  }

  public function __unset($key)
  {
    if (array_key_exists($key, $this->attributes)) {
      $this->__set($key, null);
    }
  }

  public function __toString()
  {
    return json_encode($this->jsonSerialize());
  }

  public function __debugInfo()
  {
    return $this->jsonSerialize(true);
  }

  public function jsonSerialize($useGetters = false)
  {
    $json = [];
    foreach ($this->attributes as $key => $value) {
      $hidden = array_merge($this->hidden, $this->hideDefaultFields ? $this->defaultFields : []);
      if (in_array($key, $hidden)) {
        continue;
      }
      if (in_array($key, $this->dates) && !$useGetters) {
        $json[$key] = $this->attributes[$key];
        continue;
      }

      $json[$key] = $this->__get($key);
    }

    if (is_array($this->_append))
      foreach ($this->_append as $key) {
        $json[$key] = $this->__get($key);
      }

    return $json;
  }

  public function serialize()
  {
    return serialize($this->attributes);
  }

  public function unserialize($attributes)
  {
    $this->attributes = unserialize($attributes);
  }

  public function offsetExists($key)
  {
    return method_exists($this, str_replace('_', '', 'set' . $key . 'attribute')) || array_key_exists($key, $this->attributes);
  }

  public function offsetGet($key)
  {
    return $this->__get($key);
  }

  public function offsetSet($key, $value)
  {
    $this->__set($key, $value);
  }

  public function offsetUnset($key)
  {
    $this->__unset($key);
  }

  public function update($attributes = [])
  {
    foreach ($attributes as $key => $value) {
      $this->__set($key, $value);
    }

    return $this;
  }

  public static function all()
  {
    $structureId = (new static)->directory;
    $response = NF::$capi->get('builder/structures/' . $structureId . '/entries');
    $response = json_decode($response->getBody(), true);

    return collect(array_map(function ($entry) {
      $cacheKey = 'entry/' . $entry['id'];
      NF::$cache->save($cacheKey, $entry);
      return static::generateObject($entry);
    }, $response))->values();
  }

  public static function find($id)
  {
    global $entry_override;
    global $revision_override;

    if (is_null($id)) {
      return null;
    }

    $structureId = (new static)->directory;

    if (is_array($id)) {
      $id = collect($id);
    }

    if ($id instanceof Collection) {
      $id = $id->filter();
      return $id->map(function ($id) {
        return static::find($id);
      }, $id)->values();
    }

    try {
      $data = null;
      $cacheKey = 'entry/' . $id;

      if (NF::$cache->has($cacheKey) && !isset($entry_override)) {
        $data = NF::$cache->fetch($cacheKey);
      }

      if (!$data) {
        $url = 'builder/structures/entry/' . $id;
        if (isset($entry_override) && $entry_override == $id && isset($revision_override)) {
          $url .= '/revision/' . $revision_override;
        }
        $response = NF::$capi->get($url);
        $data = json_decode($response->getBody(), true);

        if (!$data || $data['directory_id'] != $structureId) {
          $data = null;
        }

        if ($data && !isset($entry_override)) {
          NF::$cache->save($cacheKey, $data);
        }
      }

      if ($data) {
        return static::generateObject($data);
      }
    } catch (Exception $ex) { /* intentionally left blank */ }

    return null;
  }

  public static function findOrFail($id)
  {
    $entry = static::find($id);

    if (!$entry) {
      throw new Exception('Entry not found');
    }

    if (is_array($entry)) {
      if (count(array_filter($entry)) < count($entry)) {
        throw new Exception('Entry not found');
      }
    }

    return $entry;
  }

  /**
   * Resolve entry by url
   *
   * @param string $slug
   * @return static
   */
  public static function resolve($slug)
  {
    $structureId = (new static)->directory;
    $entry = resolve_entry([
      'url' => $slug . '/',
      'directory_id' => $structureId,
      'fetch' => true
    ]);
    if ($entry) {
      return static::generateObject($entry);
    }
  }

  /**
   * Resolve entry by url or fail
   *
   * @param string $slug
   * @throws Exception
   * @return static
   */
  public static function resolveOrFail($slug)
  {
    $entry = static::resolve($slug);
    if ($entry) {
      return $entry;
    }
    throw new Exception('Entry not resolved');
  }

  public static function query(...$args)
  {
    $structureId = (new static)->directory;
    $query = new StructureQuery($structureId, new static);

    return call_user_func_array([$query, 'query'], $args);
  }

  public static function count()
  {
    $structureId = (new static)->directory;
    $query = new StructureQuery($structureId, new static);

    return call_user_func_array([$query, 'count'], []);
  }

  public static function where(...$args)
  {
    $structureId = (new static)->directory;
    $query = new StructureQuery($structureId, new static);

    return call_user_func_array([$query, 'where'], $args);
  }

  public static function pluck(...$args)
  {
    $structureId = (new static)->directory;
    $query = new StructureQuery($structureId, new static);

    return call_user_func_array([$query, 'pluck'], $args);
  }

  public static function whereBetween(...$args)
  {
    $structureId = (new static)->directory;
    $query = new StructureQuery($structureId, new static);

    return call_user_func_array([$query, 'whereBetween'], $args);
  }

  public static function orderBy(...$args)
  {
    $structureId = (new static)->directory;
    $query = new StructureQuery($structureId, new static);

    return call_user_func_array([$query, 'orderBy'], $args);
  }

  public static function first()
  {
    $structureId = (new static)->directory;
    $query = new StructureQuery($structureId, new static);

    return call_user_func_array([$query, 'firstOrFail'], []);
  }

  public static function paginate(...$args)
  {
    $structureId = (new static)->directory;
    $query = new StructureQuery($structureId, new static);

    return call_user_func_array([$query, 'paginate'], $args);
  }

  public static function generateObject($data)
  {
    global $previewmode;
    if (isset($previewmode)) {
      $r = new static($data);
      if (array_key_exists('revision', $_GET) && array_key_exists('id', $_GET) && $r->id == $_GET['id']) {
        return $r->revisions[$_GET['revision']];
      } else {
        return $r;
      }
    } else {
      return new static($data);
    }
  }

  private static function bootUnlessBooted()
  {
    if (!static::$_booted) {
      static::boot();
      static::$_booted = true;
    }
  }
  private static function boot()
  { }

  public static function performHookOn($subject, $domain)
  {
    if (in_array($domain, array_keys(static::$_hooks))) {
      foreach (static::$_hooks[$domain] as $hook) {
        $hook->call($subject);
      }
    }
  }

  private static function addHook($domain, Closure $func)
  {
    if (!in_array($domain, array_keys(static::$_hooks))) {
      static::$_hooks[$domain] = [];
    }
    static::$_hooks[$domain][] = $func;
  }

  public static function __callStatic($name, $arguments)
  {

    if (in_array($name, static::$_existing_hooks) && sizeof($arguments) == 1) {
      static::addHook($name, $arguments[0]);
    }
  }
}
