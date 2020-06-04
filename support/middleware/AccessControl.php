<?php
namespace support\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AccessControl implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        /** @var Response $response */
        $response = $next($request);
        // 允许uri以 /api 开头的地址跨域访问
        if (strpos($request->path(), '/api') === 0) {
            $response->withHeaders([
                'Access-Control-Allow-Origin'      => '*',
                'Access-Control-Allow-Credentials' => 'true',
            ]);
        }
        return $response;
    }
}