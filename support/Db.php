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
namespace support;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;

/**
 * Class Db
 * @package support
 *
 * @method static \Illuminate\Database\Query\Expression raw($value)
 * @method static array select(string $query, $bindings = [], $useReadPdo = true)
 * @method static mixed selectOne(string $query, $bindings = [], $useReadPdo = true)
 * @method static array selectFromWriteConnection(string $query, $bindings = [], $useReadPdo = true)
 * @method static bool unprepared(string $query)
 * @method static bool insert(string $query, $bindings = [])
 * @method static bool statement(string $query, $bindings = [])
 * @method static int update(string $query, $bindings = [])
 * @method static int delete(string $query, $bindings = [])
 * @method static int affectingStatement(string $query, $bindings = [])
 * @method static mixed transaction(\Closure $callback, $attempts = 1)
 * @method static void beginTransaction()
 * @method static void rollBack($toLevel = null)
 * @method static void commit()
 * @method static Connection beforeExecuting(\Closure $callback)
 */
class Db extends Manager
{

}