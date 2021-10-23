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
namespace support\bootstrap\db;


use Workerman\Worker;
use Workerman\Timer;
use Webman\Bootstrap;
use support\Db;

/**
 * MySQL heartbeat.
 * Send a query regularly to prevent the MySQL connection from being inactive for a long time and being disconnected
 * by the MySQL server.
 * Add support\bootstrap\db\Heartbeat::class to config/bootstrap.php to enable it.
 * @package support\bootstrap\db
 */
class Heartbeat implements Bootstrap
{
    /**
     * @param Worker $worker
     *
     * @return void
     */
    public static function start($worker)
    {
        $connections = config('database.connections');
        if (!$connections) {
            return;
        }
        Timer::add(55, function () use ($connections){
            foreach ($connections as $key => $item) {
                Db::connection($key)->select('select 1 limit 1');
            }
        });
    }
}
