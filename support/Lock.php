<?php

namespace support;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;

class Lock
{
	/**
	 * @var LockFactory
	 */
	public static $_instance = null;

	/**
	 * @return LockFactory
	 */
	public static function instance()
	{
		if (!static::$_instance) {
			$store = new RedisStore(Redis::connection()->client());
			self::$_instance = new LockFactory($store);
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
		return static::instance()->{$name}(...$arguments);
	}
}
