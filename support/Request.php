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

/**
 * Class Request
 * @package support
 */
class Request extends \Webman\Http\Request
{
    //重写获取path用于多路由
    public function path()
    {
        if (!isset($this->_data['path'])) {
            if(config('domain.enable', false)){
                //如果开启了域名路由
                $uri = $this->uri();
                $bind = config('domain.bind', []);
                $domain = $this->host(true);
                if(isset($bind[$domain])) {
                    $uri = '/' . $bind[$domain] . $uri;
                }
            }
            $this->_data['path'] = (string)\parse_url($uri, PHP_URL_PATH);
        }
        return $this->_data['path'];
    }
}