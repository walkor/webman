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

use Webman\Route;


Route::any('/test', function ($request) {
    return response('test');
});

Route::any('/route-test', 'app\controller\Index@index');

/**
 * 示例：业务使用请自行斟酌
 *
 * 自动按照 API 路径和 HTTP Method 加载对应的控制器方法
 * 对应 app\api\controller\*
 *
 * 控制器内方法使用(Get｜Post｜Put...)作为方法后缀，没有指定的话默认加载不带后缀方法
 *
 */
Route::any(
    '/api{Endpoint:.*}',
    function ($request, $endpoint) {
        // Camelize function
        $camelize = function ($str) {
            return str_replace(
                ' ',
                '',
                ucwords(
                    preg_replace(
                        "/([^a-zA-Z0-9])/",
                        " ",
                        ucfirst($str)
                    )
                )
            );
        };
        // Request Method
        $request_method = ucfirst(strtolower($request->method()));
        // Endpoint List
        $endpoints = explode('/', $endpoint);
        // Camelize class name
        $class = 'app\api\controller\\' . $camelize($endpoints[1]);
        // Camelize method name
        $method = isset($endpoints[2]) ? $camelize($endpoints[2]) : false;
        // is class exists
        if (class_exists($class)) {
            $class = new $class();
            // Index method is default
            if (!$method) {
                $method = 'Index';
            }
            // Expected method
            $class_method = $method . $request_method;
            // Expected method first
            if (method_exists($class, $class_method)) {
                return call_user_func([$class, $class_method], $request);
            } elseif (method_exists($class, $method)) {
                return call_user_func([$class, $method], $request);
            }
        }

        //404
        return json(['code' => 404, 'message' => 'API Not Found'])->withStatus(404);
    }
);