<?php
/**
 * This file is part of webman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author    walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link      http://www.workerman.net/
 * @license   http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace support\bootstrap;

use Webman\Bootstrap;
use Workerman\Stomp\Client;

/**
 * Class Stomp
 * @package support
 *
 * Strings methods
 * @method static void send($queue, $body, array $headers = [])
 */
class Stomp implements Bootstrap
{

    /**
     * @var Client[]
     */
    protected static $_connections = null;

    /**
     * @param \Workerman\Worker $worker
     * @return void
     */
    public static function start($worker)
    {
        $config = config('stomp', []);
        foreach ($config as $name => $items)
        {
            $host = $items['host'];
            $options = $items['options'];
            $client = new Client($host, $options);
            $client->connect();
            static::$_connections[$name] = $client;
        }
    }

    /**
     * @param string $name
     * @return Client
     */
    public static function connection($name = 'default') {
        if (!isset(static::$_connections[$name])) {
            throw new \RuntimeException("Stomp connection $name not found");
        }
        return static::$_connections[$name];
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::connection('default')->{$name}(... $arguments);
    }
}
