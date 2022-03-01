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

namespace support\view;

use Webman\View;

/**
 * Class Raw
 * @package support\view
 */
class Raw implements View
{
    /**
     * @var array
     */
    protected static $_vars = [];

    /**
     * @param $name
     * @param null $value
     */
    public static function assign($name, $value = null)
    {
        static::$_vars = \array_merge(static::$_vars, \is_array($name) ? $name : [$name => $value]);
    }

    /**
     * @param $template
     * @param $vars
     * @param null $app
     * @return string
     */
    public static function render($template, $vars, $app = null)
    {
        static $view_suffix;
        $view_suffix = $view_suffix ?: \config('view.view_suffix', 'html');
        $app = $app === null ? \request()->app : $app;
        if ($app === '') {
            $view_path = \app_path() . "/view/$template.$view_suffix";
        } else {
            $view_path = \app_path() . "/$app/view/$template.$view_suffix";
        }
        \extract(static::$_vars);
        \extract($vars);
        \ob_start();
        // Try to include php file.
        try {
            include $view_path;
        } catch (\Throwable $e) {
            echo $e;
        }
        static::$_vars = [];
        return \ob_get_clean();
    }

}
