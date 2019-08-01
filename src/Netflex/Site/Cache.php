<?php

namespace Netflex\Site;

use NF;
use Exception;
use ReflectionFunction;
use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Files\Config as FilesConfig;
use Phpfastcache\Drivers\Memcached\Config as MemcachedConfig;


class Cache
{
  /** @var int */
  const TTL = 3600;

  /** @var string */
  private static $key;

  /** @var \Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface */
  private static $cache;

  public function __construct()
  {
    self::$key = NF::$sitename;

    $config = null;
    $driver = class_exists('Memcached') ? 'memcached' : 'files';

    switch ($driver) {
      case 'memcached':
        $memcached_hostname = getenv('MEMCACHED_HOST') ? getenv('MEMCACHED_HOST') : '127.0.0.1';
        $memcached_port = getenv('MEMCACHED_PORT') ? intval(getenv('MEMCACHED_PORT')) : 11211;

        $config = new MemcachedConfig([
          'host' => $memcached_hostname,
          'port' => $memcached_port,
        ]);

        break;
      case 'files':
        if (!file_exists(NF::$cacheDir . 'cache/')) {
          mkdir(NF::$cacheDir . 'cache/', 0755, true);
        }

        $config = new FilesConfig([
          'path' => NF::$cacheDir . 'cache/'
        ]);

        break;
      default:
        throw new Exception('Invalid Cache driver');
    }

    self::$cache = CacheManager::getInstance($driver, $config);
  }

  /**
   * Creates a prefixed cache key
   *
   * @param string $key
   * @return string
   */
  public function getCacheKey($key)
  {
    return md5(self::$key . $key);
  }

  /**
   * This function is only an alias for fetch.
   *
   * @see fetch
   * @param string $key
   * @return mixed
   */
  public function get($key)
  {
    return $this->fetch($key);
  }

  /**
   * This function is only an alias for save.
   *
   * @see save
   * @return bool
   */
  public function set($key, $value, $_ = false, $ttl)
  {
    return $this->save($key, $value, $ttl, null);
  }

  /**
   * This function is only an alias for save.
   *
   * @see save
   * @return bool
   */
  public function add($key, $value, $_ = false, $ttl)
  {
    return $this->save($key, $value, $ttl, null);
  }

  /**
   * Fetches item from cache by $key
   *
   * @param string $key
   * @return mixed
   */
  public function fetch($key)
  {
    $item = self::$cache->getItem(self::getCacheKey($key));
    $item = unserialize($item->get());
    return $item;
  }

  /**
   * Checks is $key exists in the cache
   *
   * @param string $key
   * @return bool
   */
  public function has($key)
  {
    $item = self::$cache->getItem(self::getCacheKey($key));
    return !is_null($item->get());
  }

  /**
   * Stores an item in the cache
   *
   * @param string $key
   * @param mixed $value
   * @param int $ttl = 0
   * @param string $tag = null
   * @return bool
   */
  public function save($key, $value, $ttl = null, $tag = null)
  {
    $min_ttl = 3500;
    $max_ttl = 3800;

    if (is_null($ttl) || ($ttl > $min_ttl && $ttl < $max_ttl)) {
      $ttl = rand($min_ttl, $max_ttl);
    }

    $value = serialize($value);
    $item = self::$cache->getItem(self::getCacheKey($key));
    $item->set($value)->expiresAfter($ttl);
    return self::$cache->save($item);
  }

  /**
   * Stores an array of items in the cache
   *
   * @param array $items
   * @return void
   */
  public function saveMultiple(array $items)
  {
    foreach ($items as $item) {
      $this->save($item['key'], $item['value'], $item['ttl'], $item['tag']);
    }
  }

  /**
   * Deletes the item from the cache
   *
   * @param string $key
   * @return bool
   */
  public function delete($key)
  {
    $key = self::getCacheKey($key);
    return self::$cache->deleteItem($key);
  }

  /**
   * Deletes an array of items from the cache
   *
   * @param array $keys
   * @return void
   */
  public function deleteMultiple(array $keys)
  {
    foreach ($keys as $key) {
      $this->delete($key);
    }
  }

  /**
   * Deletes items by tag
   *
   * @param string|array $tag
   * @return bool
   */
  public function deleteTag($tag)
  {
    if (is_array($tag)) {
      return self::$cache->deleteItemsByTags($tag);
    }

    return self::$cache->deleteItemsByTag($tag);
  }

  /**
   * Purges all items from the cache
   *
   * @return bool
   */
  public function purge()
  {
    return self::$cache->clear();
  }

  /**
   * One line fetch or set cache function
   *
   * @param $key string Name of the cache key
   * @param $ttl int Cache time to live
   * @param $callback function A function that resolves the value if it is not already cached
   */
  public function resolve($key, $ttl = 3600, $callback) {
    if ($this->has($key)) {
      return $this->get($key);
    }

    $ttlValue = null;
    $ttlFunction = function (int $value) use (&$ttlValue) {
      $ttlValue = $value;
    };

    $args = (new ReflectionFunction($callback))->getNumberOfParameters();

    if ($args == 1) {
      $response = $callback($ttlFunction);
      $ttlValue = is_int($ttlValue) ? max(1, $ttlValue) : NULL;
      NF::debug($ttlValue, 'Cache->resolve(' . $key . ') dynamic duration');
    } else {
      $response = $callback();
    }

    $this->set($key, $response, '_', $ttlValue ?? $ttl);

    return $this->get($key);
  }
}
