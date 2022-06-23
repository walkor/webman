#!/usr/bin/env php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Protocols\Http;
use Workerman\Connection\TcpConnection;
use Webman\App;
use Webman\Config;
use Webman\Route;
use Webman\Middleware;
use Dotenv\Dotenv;
use support\Request;
use support\Log;
use support\Container;

ini_set('display_errors', 'on');
error_reporting(E_ALL);

if (class_exists('Dotenv\Dotenv') && file_exists(base_path() . '/.env')) {
    if (method_exists('Dotenv\Dotenv', 'createUnsafeImmutable')) {
        Dotenv::createUnsafeImmutable(base_path())->load();
    } else {
        Dotenv::createMutable(base_path())->load();
    }
}

Config::load(config_path(), ['route', 'container']);

$error_reporting = config('app.error_reporting');
if (isset($error_reporting)) {
    error_reporting($error_reporting);
}

if ($timezone = config('app.default_timezone')) {
    date_default_timezone_set($timezone);
}

$runtime_logs_path = runtime_path() . DIRECTORY_SEPARATOR . 'logs';
if ( !file_exists($runtime_logs_path) || !is_dir($runtime_logs_path) ) {
    if (!mkdir($runtime_logs_path,0777,true)) {
        throw new \RuntimeException("Failed to create runtime logs directory. Please check the permission.");
    }
}

$runtime_views_path = runtime_path() . DIRECTORY_SEPARATOR . 'views';
if ( !file_exists($runtime_views_path) || !is_dir($runtime_views_path) ) {
    if (!mkdir($runtime_views_path,0777,true)) {
        throw new \RuntimeException("Failed to create runtime views directory. Please check the permission.");
    }
}

Worker::$onMasterReload = function () {
    if (function_exists('opcache_get_status')) {
        if ($status = opcache_get_status()) {
            if (isset($status['scripts']) && $scripts = $status['scripts']) {
                foreach (array_keys($scripts) as $file) {
                    opcache_invalidate($file, true);
                }
            }
        }
    }
};

$config = config('server');
Worker::$pidFile = $config['pid_file'];
Worker::$stdoutFile = $config['stdout_file'];
Worker::$logFile = $config['log_file'];
Worker::$eventLoopClass = $config['event_loop'] ?? '';
TcpConnection::$defaultMaxPackageSize = $config['max_package_size'] ?? 10 * 1024 * 1024;
if (property_exists(Worker::class, 'statusFile')) {
    Worker::$statusFile = $config['status_file'] ?? '';
}
if (property_exists(Worker::class, 'stopTimeout')) {
    Worker::$stopTimeout = $config['stop_timeout'] ?? 2;
}

if ($config['listen']) {
    $worker = new Worker($config['listen'], $config['context']);
    $property_map = [
        'name',
        'count',
        'user',
        'group',
        'reusePort',
        'transport',
        'protocol'
    ];
    foreach ($property_map as $property) {
        if (isset($config[$property])) {
            $worker->$property = $config[$property];
        }
    }

    $worker->onWorkerStart = function ($worker) {
        require_once base_path() . '/support/bootstrap.php';
        $app = new App($worker, Container::instance(), Log::channel('default'), app_path(), public_path());
        Http::requestClass(config('app.request_class', config('server.request_class', Request::class)));
        $worker->onMessage = [$app, 'onMessage'];
    };
}

// Windows does not support custom processes.
if (\DIRECTORY_SEPARATOR === '/') {
    foreach (config('process', []) as $process_name => $config) {
        worker_start($process_name, $config);
    }
    foreach (config('plugin', []) as $firm => $projects) {
        foreach ($projects as $name => $project) {
            foreach ($project['process'] ?? [] as $process_name => $config) {
                worker_start("plugin.$firm.$name.$process_name", $config);
            }
        }
    }
}

Worker::runAll();
