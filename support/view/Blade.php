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

use Jenssegers\Blade\Blade as BladeView;
use Webman\View;

/**
 * Class Blade
 * composer require jenssegers/blade
 * @package support\view
 */
class Blade implements View
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
     * @param string $app
     * @return mixed
     */
    public static function render($template, $vars, $app = null)
    {
        static $views = [];
        $app = $app === null ? \request()->app : $app;
        if (!isset($views[$app])) {
            $view_path = $app === '' ? \app_path() . '/view' : \app_path() . "/$app/view";
            $views[$app] = new BladeView($view_path, \runtime_path() . '/views');
        }
        $vars = \array_merge(static::$_vars, $vars);
        $content = $views[$app]->render($template, $vars);
        static::$_vars = [];
        return $content;
    }
}
