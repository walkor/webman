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

namespace support\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AccessControlTest implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        // 允许uri以 /api 开头的地址跨域访问
        if (strpos($request->path(), '/api') === 0) {
            // 如果是options请求，不处理业务
            if ($request->method() == 'OPTIONS') {
                $response = response('');
            } else {
                $response = $next($request);
            }
            $response->withHeaders([
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET,POST,PUT,DELETE,OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type,Authorization,X-Requested-With,Accept,Origin',
            ]);
        } else {
            $response = $next($request);
        }
        return $response;
    }
}
