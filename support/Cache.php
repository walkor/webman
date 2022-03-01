<?php

namespace support;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * Class Cache
 * @package support\bootstrap
 *
 * Strings methods
 * @method static mixed get($key, $default = null)
 * @method static bool set($key, $value, $ttl = null)
 * @method static bool delete($key)
 * @method static bool clear()
 * @method static iterable getMultiple($keys, $default = null)
 * @method static bool setMultiple($values, $ttl = null)
 * @method static bool deleteMultiple($keys)
 * @method static bool has($key)
 */
class Cache
{
    /**
     * @var Psr16Cache
     */
    public static $_instance = null;

    /**
     * @return Psr16Cache
     */
    public static function instance()
    {
        if (!static::$_instance) {
            $adapter = new RedisAdapter(Redis::connection()->client());
            self::$_instance = new Psr16Cache($adapter);
        }
        return static::$_instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(... $arguments);
    }
}
