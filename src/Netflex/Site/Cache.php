<?php
namespace Netflex\Site;

use NF;
use Exception;
use Phpfastcache\CacheManager;
use Phpfastcache\Drivers\Files\Config as FilesConfig;
use Phpfastcache\Drivers\Memcached\Config as MemcachedConfig;


class Cache
{

	private static $key;
	private static $cache;

	public function __construct() {

    self::$key = NF::$sitename;

    $config = null;
    $driver = class_exists('Memcached') ? 'memcached' : 'files';

    switch ($driver) {
      case 'memcached':
        $memcached_hostname = getenv("MEMCACHED_HOST") ? getenv("MEMCACHED_HOST") : '127.0.0.1';
        $memcached_port = getenv("MEMCACHED_PORT") ? intval(getenv("MEMCACHED_PORT")) : 11211;

        $config = new MemcachedConfig([
          'host' => $memcached_hostname,
          'port' => $memcached_port,
        ]);
        break;
      case 'files':
        if (!file_exists(NF::$cacheDir . 'cache/')) {
          mkdir(NF::$cacheDir. 'cache/', 0755, true);
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

	public function getCacheKey($key) {
		return md5(self::$key . $key);
  }

  public function get ($key) {
    return $this->fetch($key);
  }

  /**
   * This function is only an alias for save.
   * @see save
   * @return void
   */
  public function set($key, $value, $_=false, $ttl) {
    return $this->save($key, $value, $ttl, null);
  }

  public function add ($key, $value, $_ = false, $ttl) {
    return $this->save($key, $value, $ttl, null);
  }

	public function fetch($key) {
    $item = self::$cache->getItem(self::getCacheKey($key));
    $item = unserialize($item->get());
    return $item;
  }

  public function has($key) {
    $item = self::$cache->getItem(self::getCacheKey($key));
    return !is_null($item->get());
  }

  public function save($key, $value, $ttl = 0, $tag = null) {
    $value = serialize($value);
    $item = self::$cache->getItem(self::getCacheKey($key));
    $item->set($value)->expiresAfter($ttl);
    self::$cache->save($item);
	}

	public function saveMultiple(array $items) {
		foreach($items as $item) {
			$this->save($item['key'], $item['value'], $item['ttl'], $item['tag']);
		}
	}

  public function delete($key) {
    $key = self::getCacheKey($key);
    self::$cache->deleteItem($key);
  }

	public function deleteMultiple(array $keys) {
		foreach($keys as $key) {
			$this->delete($key);
		}
	}

	public function deleteTag($tag) {
		self::$cache->deleteItemsByTags($tags);
	}

	public function purge() {
		self::$cache->clear();
	}

}
