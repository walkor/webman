<?php

use Dotenv\Dotenv;
use Webman\Bootstrap;
use Webman\Config;

require_once __DIR__ . '/../vendor/autoload.php';

if (method_exists('Dotenv\Dotenv', 'createUnsafeImmutable')) {
    Dotenv::createUnsafeImmutable(base_path())->load();
} else {
    Dotenv::createMutable(base_path())->load();
}
Config::load(config_path(), ['route', 'container']);
if ($timezone = config('app.default_timezone')) {
    date_default_timezone_set($timezone);
}
foreach (config('autoload.files', []) as $file) {
    include_once $file;
}
foreach (config('bootstrap', []) as $class_name) {
    /** @var Bootstrap $class_name */
    $class_name::start(null);
}