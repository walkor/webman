<?php
require_once __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Webman\App;
use Webman\Config;
use support\Request;
use Dotenv\Dotenv;

Dotenv::createMutable(base_path())->load();
Config::load(config_path(), ['route']);
$config = config('server');

Worker::$onMasterReload = function (){
    foreach (array_keys(opcache_get_status()['scripts']) as $file) {
        opcache_invalidate($file, true);
    }
};

Worker::$pidFile    = $config['pid_file'];
Worker::$stdoutFile = $config['stdout_file'];

$worker = new Worker($config['listen'], $config['context']);
$property_map = [
    'name',
    'count',
    'user',
    'group',
    'reusePort',
    'transport',
];
foreach ($property_map as $property) {
    if (isset($config[$property])) {
        $worker->$property = $config[$property];
    }
}

$worker->onWorkerStart = function ($worker) {
    Dotenv::createMutable(base_path())->load();
    Config::reload(config_path(), ['route']);
    $app = new App($worker, Request::class);
    $worker->onMessage = [$app, 'onMessage'];
};


foreach (config('process', []) as $process_name => $config) {
    $worker = new Worker($config['listen'] ?? null, $config['context'] ?? []);
    $property_map = [
        'count',
        'user',
        'group',
        'reloadable',
        'reusePort',
        'transport',
        'protocol',
    ];
    $worker->name = $process_name;
    foreach ($property_map as $property) {
        if (isset($config[$property])) {
            $worker->$property = $config[$property];
        }
    }

    $worker->onWorkerStart = function ($worker) use ($config) {
        Dotenv::createMutable(base_path())->load();
        Config::reload(config_path(), ['route']);

        $bootstrap = $config['bootstrap'] ?? config('bootstrap', []);
        if (!in_array(support\bootstrap\Log::class, $bootstrap)) {
            $bootstrap[] = support\bootstrap\Log::class;
        }
        foreach ($bootstrap as $class_name) {
            /** @var \Webman\Bootstrap $class_name */
            $class_name::start($worker);
        }

        foreach ($config['services'] ?? [] as $server) {
            if (!class_exists($server['class'])) {
                echo "process error: class {$config['class']} not exists\r\n";
                continue;
            }
            $listen = new Worker($server['listen'] ?? null, $server['context'] ?? []);
            if (isset($server['listen'])) {
                echo "listen: {$server['listen']}\n";
            }
            $class = singleton($server['class'], $server['constructor'] ?? []);
            init_worker($listen, $class);
            $listen->listen();
        }

        if (isset($config['class'])) {
            if (!class_exists($config['class'])) {
                echo "process error: class {$config['class']} not exists\r\n";
                return;
            }
            $class = singleton($config['class'], $config['constructor'] ?? []);

            init_worker($worker, $class);
        }

    };
}

function init_worker($worker, $class)
{
    $callback_map = [
        'onConnect',
        'onMessage',
        'onClose',
        'onError',
        'onBufferFull',
        'onBufferDrain',
        'onWorkerStop',
        'onWebSocketConnect'
    ];
    foreach ($callback_map as $name) {
        if (method_exists($class, $name)) {
            $worker->$name = [$class, $name];
        }
    }
    if (method_exists($class, 'onWorkerStart')) {
        call_user_func([$class, 'onWorkerStart'], $worker);
    }
}

Worker::runAll();