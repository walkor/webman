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

use support\MiddlewareInterface;
use support\Request;
use support\Response;

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
