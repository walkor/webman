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

use support\Request;
use support\Response;
use support\view\Raw;
use Webman\App;
use Webman\Config;
use Webman\Exception\ClassNotFoundException;

define('BASE_PATH', realpath(__DIR__ . '/../'));

/**
 * @param int $status
 * @param array $headers
 * @param string $body
 * @return Response
 */
function response($body = '', $status = 200, $headers = array())
{
    return new Response($status, $headers, $body);
}

/**
 * @return string
 */
function base_path()
{
    return BASE_PATH;
}

function app_path()
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'app';
}

function public_path()
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'public';
}

function config_path()
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'config';
}

function runtime_path()
{
    return BASE_PATH . DIRECTORY_SEPARATOR . 'runtime';
}

/**
 * @param $name
 * @return
 */
function singleton($name, $constructor = [])
{
    static $instances;
    if (isset($instances[$name])) {
        return $instances[$name];
    }
    if (\class_exists($name)) {
        return $instances[$name] = new $name(... $constructor);
    }
    throw new ClassNotFoundException("Class $name not found");
}

/**
 * @param $data
 * @param int $options
 * @return Response
 */
function json($data, $options = JSON_UNESCAPED_UNICODE)
{
    return new Response(200, ['Content-Type' => 'application/json'], json_encode($data, $options));
}

/**
 * @param $xml
 * @return Response
 */
function xml($xml)
{
    if ($xml instanceof SimpleXMLElement) {
        $xml = $xml->asXML();
    }
    return new Response(200, ['Content-Type' => 'text/xml'], $xml);
}

/**
 * @param $data
 * @param string $callback_name
 * @return Response
 */
function jsonp($data, $callback_name = 'callback')
{
    if (!is_scalar($data) && null !== $data) {
        $data = json_encode($data);
    }
    return new Response(200, [], "$callback_name($data)");
}

/**
 * @param $location
 * @param int $status
 * @param array $headers
 * @return Response
 */
function redirect($location, $status = 302, $headers = [])
{
    $response = new Response($status, ['Location' => $location]);
    if (!empty($headers)) {
        $response->withHeaders($headers);
    }
    return $response;
}

/**
 * @param $template
 * @param array $vars
 * @param null $app
 * @return string
 */
function view($template, $vars = [], $app = null)
{
    static $handler;
    if (null === $handler) {
        $handler = config('view.handler');
    }
    return $handler::render($template, $vars, $app);
}

/**
 * @return Request
 */
function request()
{
    return App::request();
}

/**
 * @param $key
 * @param null $default
 * @return mixed
 */
function config($key = null, $default = null)
{
    return Config::get($key, $default);
}

if (!function_exists('env')) {
    /**
     * @param $key
     * @param null $default
     * @return array|bool|false|mixed|string
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}