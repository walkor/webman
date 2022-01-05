<?php
namespace support;

class Ext
{
    public static function install($event)
    {
        $autoload = $event->getOperation()->getPackage()->getAutoload();
        if (!isset($autoload['psr-4'])) {
            return;
        }
        $namespace = key($autoload['psr-4']);
        $install_function = "\\{$namespace}Install::install";
        require_once __DIR__ . '/../vendor/autoload.php';
        if (is_callable($install_function)) {
            $install_function();
        }
    }

    public static function uninstall($event)
    {
        $autoload = $event->getOperation()->getPackage()->getAutoload();
        if (!isset($autoload['psr-4'])) {
            return;
        }
        $namespace = key($autoload['psr-4']);
        $uninstall_function = "\\{$namespace}Install::uninstall";
        require_once __DIR__ . '/../vendor/autoload.php';
        if (is_callable($uninstall_function)) {
            $uninstall_function();
        }
    }
}