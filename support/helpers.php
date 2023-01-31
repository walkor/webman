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

use support\Container;
use support\Request;
use support\Response;
use support\Translation;
use support\view\Raw;
use support\view\Blade;
use support\view\ThinkPHP;
use support\view\Twig;
use Workerman\Worker;
use Webman\App;
use Webman\Config;
use Webman\Route;

// Webman version
const WEBMAN_VERSION = '1.4';

// Project base path
define('BASE_PATH', dirname(__DIR__));

/**
 * return the program execute directory
 * @param string $path
 * @return string
 */
function run_path(string $path = ''): string
{
    static $runPath = '';
    if (!$runPath) {
        $runPath = \is_phar() ? \dirname(\Phar::running(false)) : BASE_PATH;
    }
    return \path_combine($runPath, $path);
}

/**
 * if the param $path equal false,will return this program current execute directory
 * @param string|false $path
 * @return string
 */
function base_path($path = ''): string
{
    if (false === $path) {
        return \run_path();
    }
    return \path_combine(BASE_PATH, $path);
}

/**
 * App path
 * @param string $path
 * @return string
 */
function app_path(string $path = ''): string
{
    return \path_combine(BASE_PATH . DIRECTORY_SEPARATOR . 'app', $path);
}

/**
 * Public path
 * @param string $path
 * @return string
 */
function public_path(string $path = ''): string
{
    static $publicPath = '';
    if (!$publicPath) {
        $publicPath = \config('app.public_path') ? : \run_path('public');
    }
    return \path_combine($publicPath, $path);
}

/**
 * Config path
 * @param string $path
 * @return string
 */
function config_path(string $path = ''): string
{
    return \path_combine(BASE_PATH . DIRECTORY_SEPARATOR . 'config', $path);
}

/**
 * Runtime path
 * @param string $path
 * @return string
 */
function runtime_path(string $path = ''): string
{
    static $runtimePath = '';
    if (!$runtimePath) {
        $runtimePath = \config('app.runtime_path') ? : \run_path('runtime');
    }
    return \path_combine($runtimePath, $path);
}

/**
 * Generate paths based on given information
 * @param string $front
 * @param string $back
 * @return string
 */
function path_combine(string $front, string $back): string
{
    return $front . ($back ? (DIRECTORY_SEPARATOR . ltrim($back, DIRECTORY_SEPARATOR)) : $back);
}

/**
 * Response
 * @param int $status
 * @param array $headers
 * @param string $body
 * @return Response
 */
function response(string $body = '', int $status = 200, array $headers = []): Response
{
    return new Response($status, $headers, $body);
}

/**
 * Json response
 * @param $data
 * @param int $options
 * @return Response
 */
function json($data, int $options = JSON_UNESCAPED_UNICODE): Response
{
    return new Response(200, ['Content-Type' => 'application/json'], \json_encode($data, $options));
}

/**
 * Xml response
 * @param $xml
 * @return Response
 */
function xml($xml): Response
{
    if ($xml instanceof SimpleXMLElement) {
        $xml = $xml->asXML();
    }
    return new Response(200, ['Content-Type' => 'text/xml'], $xml);
}

/**
 * Jsonp response
 * @param $data
 * @param string $callbackName
 * @return Response
 */
function jsonp($data, string $callbackName = 'callback'): Response
{
    if (!\is_scalar($data) && null !== $data) {
        $data = \json_encode($data);
    }
    return new Response(200, [], "$callbackName($data)");
}

/**
 * Redirect response
 * @param string $location
 * @param int $status
 * @param array $headers
 * @return Response
 */
function redirect(string $location, int $status = 302, array $headers = []): Response
{
    $response = new Response($status, ['Location' => $location]);
    if (!empty($headers)) {
        $response->withHeaders($headers);
    }
    return $response;
}

/**
 * View response
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 */
function view(string $template, array $vars = [], string $app = null): Response
{
    $request = \request();
    $plugin =  $request->plugin ?? '';
    $handler = \config($plugin ? "plugin.$plugin.view.handler" : 'view.handler');
    return new Response(200, [], $handler::render($template, $vars, $app));
}

/**
 * Raw view response
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 * @throws Throwable
 */
function raw_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], Raw::render($template, $vars, $app));
}

/**
 * Blade view response
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 */
function blade_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], Blade::render($template, $vars, $app));
}

/**
 * Think view response
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 */
function think_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], ThinkPHP::render($template, $vars, $app));
}

/**
 * Twig view response
 * @param string $template
 * @param array $vars
 * @param string|null $app
 * @return Response
 */
function twig_view(string $template, array $vars = [], string $app = null): Response
{
    return new Response(200, [], Twig::render($template, $vars, $app));
}

/**
 * Get request
 * @return \Webman\Http\Request|Request|null
 */
function request()
{
    return App::request();
}

/**
 * Get config
 * @param string|null $key
 * @param $default
 * @return array|mixed|null
 */
function config(string $key = null, $default = null)
{
    return Config::get($key, $default);
}

/**
 * Create url
 * @param string $name
 * @param ...$parameters
 * @return string
 */
function route(string $name, ...$parameters): string
{
    $route = Route::getByName($name);
    if (!$route) {
        return '';
    }

    if (!$parameters) {
        return $route->url();
    }

    if (\is_array(\current($parameters))) {
        $parameters = \current($parameters);
    }

    return $route->url($parameters);
}

/**
 * Session
 * @param mixed $key
 * @param mixed $default
 * @return mixed
 */
function session($key = null, $default = null)
{
    $session = \request()->session();
    if (null === $key) {
        return $session;
    }
    if (\is_array($key)) {
        $session->put($key);
        return null;
    }
    if (\strpos($key, '.')) {
        $keyArray = \explode('.', $key);
        $value = $session->all();
        foreach ($keyArray as $index) {
            if (!isset($value[$index])) {
                return $default;
            }
            $value = $value[$index];
        }
        return $value;
    }
    return $session->get($key, $default);
}

/**
 * Translation
 * @param string $id
 * @param array $parameters
 * @param string|null $domain
 * @param string|null $locale
 * @return string
 */
function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
{
    $res = Translation::trans($id, $parameters, $domain, $locale);
    return $res === '' ? $id : $res;
}

/**
 * Locale
 * @param string|null $locale
 * @return string
 */
function locale(string $locale = null): string
{
    if (!$locale) {
        return Translation::getLocale();
    }
    Translation::setLocale($locale);
    return $locale;
}

/**
 * 404 not found
 * @return Response
 */
function not_found(): Response
{
    return new Response(404, [], \file_get_contents(public_path() . '/404.html'));
}

/**
 * Copy dir
 * @param string $source
 * @param string $dest
 * @param bool $overwrite
 * @return void
 */
function copy_dir(string $source, string $dest, bool $overwrite = false)
{
    if (\is_dir($source)) {
        if (!is_dir($dest)) {
            \mkdir($dest);
        }
        $files = \scandir($source);
        foreach ($files as $file) {
            if ($file !== "." && $file !== "..") {
                \copy_dir("$source/$file", "$dest/$file");
            }
        }
    } else if (\file_exists($source) && ($overwrite || !\file_exists($dest))) {
        \copy($source, $dest);
    }
}

/**
 * Remove dir
 * @param string $dir
 * @return bool
 */
function remove_dir(string $dir): bool
{
    if (\is_link($dir) || \is_file($dir)) {
        return \unlink($dir);
    }
    $files = \array_diff(\scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (\is_dir("$dir/$file") && !\is_link($dir)) ? \remove_dir("$dir/$file") : \unlink("$dir/$file");
    }
    return \rmdir($dir);
}

/**
 * Bind worker
 * @param $worker
 * @param $class
 */
function worker_bind($worker, $class)
{
    $callbackMap = [
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWebSocketConnect'
    ];
    foreach ($callbackMap as $name) {
        if (\method_exists($class, $name)) {
            $worker->$name = [$class, $name];
        }
    }
    if (\method_exists($class, 'onWorkerStart')) {
        \call_user_func([$class, 'onWorkerStart'], $worker);
    }
}

/**
 * Start worker
 * @param $processName
 * @param $config
 * @return void
 */
function worker_start($processName, $config)
{
    $worker = new Worker($config['listen'] ?? null, $config['context'] ?? []);
    $propertyMap = [
        'count',
        'user',
        'group',
        'reloadable',
        'reusePort',
        'transport',
        'protocol',
    ];
    $worker->name = $processName;
    foreach ($propertyMap as $property) {
        if (isset($config[$property])) {
            $worker->$property = $config[$property];
        }
    }

    $worker->onWorkerStart = function ($worker) use ($config) {
        require_once \base_path() . '/support/bootstrap.php';

        foreach ($config['services'] ?? [] as $server) {
            if (!\class_exists($server['handler'])) {
                echo "process error: class {$server['handler']} not exists\r\n";
                continue;
            }
            $listen = new Worker($server['listen'] ?? null, $server['context'] ?? []);
            if (isset($server['listen'])) {
                echo "listen: {$server['listen']}\n";
            }
            $instance = Container::make($server['handler'], $server['constructor'] ?? []);
            \worker_bind($listen, $instance);
            $listen->listen();
        }

        if (isset($config['handler'])) {
            if (!\class_exists($config['handler'])) {
                echo "process error: class {$config['handler']} not exists\r\n";
                return;
            }

            $instance = Container::make($config['handler'], $config['constructor'] ?? []);
            \worker_bind($worker, $instance);
        }
    };
}

/**
 * Get realpath
 * @param string $filePath
 * @return string
 */
function get_realpath(string $filePath): string
{
    if (\strpos($filePath, 'phar://') === 0) {
        return $filePath;
    } else {
        return \realpath($filePath);
    }
}

/**
 * Is phar
 * @return bool
 */
function is_phar(): bool
{
    return \class_exists(\Phar::class, false) && Phar::running();
}

/**
 * Get cpu count
 * @return int
 */
function cpu_count(): int
{
    // Windows does not support the number of processes setting.
    if (\DIRECTORY_SEPARATOR === '\\') {
        return 1;
    }
    $count = 4;
    if (\is_callable('shell_exec')) {
        if (\strtolower(PHP_OS) === 'darwin') {
            $count = (int)\shell_exec('sysctl -n machdep.cpu.core_count');
        } else {
            $count = (int)\shell_exec('nproc');
        }
    }
    return $count > 0 ? $count : 4;
}
