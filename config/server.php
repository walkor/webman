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

return [
    'listen'          => 'http://0.0.0.0:8787',
    'ssl'             => false,
    'context'         => [],
    'process_name'    => 'webman',
    'process_count'   => env('SERVER_PROCESS_COUNT', shell_exec('nproc') ? (int)shell_exec('nproc') * 2 : 4),
    'user'            => env('SERVER_PROCESS_USER', ''),
    'group'           => env('SERVER_PROCESS_GROUUP', ''),
    'pid_file'        => runtime_path() . '/webman.pid',
    'max_request'     => 1000000,
    'stdout_file'     => runtime_path() . '/logs/stdout.log',
];