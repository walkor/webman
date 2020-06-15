<?php
namespace support\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class AuthCheckTest implements MiddlewareInterface
{
    public function process(Request $request, callable $next) : Response
    {
        $session = $request->session();
        if (!$session->get('userinfo')) {
            return redirect('/user/login');
        }
        return $next($request);
    }
}