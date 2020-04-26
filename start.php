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

$worker = new Worker($config['listen'], $config['context']);
$worker->count      = $config['process_count'];
$worker->name       = $config['process_name'];
Worker::$pidFile    = $config['pid_file'];
Worker::$stdoutFile = $config['stdout_file'];
$config['ssl'] && $worker->transport = 'ssl';
$config['user'] && $worker->user = $config['user'];
$config['group'] && $worker->group = $config['group'];

$worker->onWorkerStart = function ($worker) {
    Dotenv::createMutable(base_path())->load();
    Config::reload(config_path(), ['route', 'server']);
    $app = new App($worker, Request::class);
    $worker->onMessage = [$app, 'onMessage'];
};

Worker::runAll();