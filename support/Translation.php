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

use Symfony\Component\Translation\Translator;
use Webman\Exception\NotFoundException;

/**
 * Class Translation
 * @package support
 * @method static string trans(?string $id, array $parameters = [], string $domain = null, string $locale = null)
 * @method static void setLocale(string $locale)
 * @method static string getLocale()
 */
class Translation
{

    /**
     * @var Translator
     */
    protected static $_instance;

    /**
     * @return Translator
     * @throws NotFoundException
     */
    public static function instance()
    {
        if (!static::$_instance) {
            $config = config('translation', []);
            if (!$translations_path = realpath($config['path'])) {
                throw new NotFoundException("File {$config['path']} not found");
            }

            static::$_instance = $translator = new Translator($config['locale']);
            $translator->setFallbackLocales($config['fallback_locale']);

            $classes = [
                'Symfony\Component\Translation\Loader\PhpFileLoader' => [
                    'extension' => '.php',
                    'format' => 'phpfile'
                ],
                'Symfony\Component\Translation\Loader\PoFileLoader' => [
                    'extension' => '.po',
                    'format' => 'pofile'
                ]
            ];

            foreach ($classes as $class => $opts)
            {
                $translator->addLoader($opts['format'], new $class);
                foreach (glob($translations_path . DIRECTORY_SEPARATOR . '*' . DIRECTORY_SEPARATOR . '*' . $opts['extension']) as $file) {
                    $domain = basename($file, $opts['extension']);
                    $dir_name = pathinfo($file, PATHINFO_DIRNAME);
                    $locale = substr(strrchr($dir_name, DIRECTORY_SEPARATOR), 1);
                    if ($domain && $locale) {
                        $translator->addResource($opts['format'], $file, $locale, $domain);
                    }
                }
            }
        }
        return static::$_instance;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        return static::instance()->{$name}(... $arguments);
    }
}
