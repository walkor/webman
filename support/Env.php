<?php

namespace support;

class Env
{
    /**
     * 环境变量数据
     *
     * @var array
     */
    protected static $data = [];

    /**
     * 读取环境变量定义文件
     *
     * @param string $file 环境变量定义文件
     * @return void
     */
    public static function load(string $file): void {
        if (!file_exists($file)) {
            return;
        }
        $env = parse_ini_file($file, true);
        if (isset($env['ENV']) && file_exists(base_path().'/.env.'.$env['ENV'])) {
            $_env = parse_ini_file(base_path().'/.env.'.$env['ENV'], true);
            $env = array_merge($env, $_env);
        }
        self::set($env);
    }

    /**
     * 获取环境变量值
     *
     * @param string|null $name 环境变量名
     * @param mixed $default 默认值
     */
    public static function get(string $name = null, $default = null) {
        if ($name === null) {
            return self::$data;
        }
        $name = strtoupper(str_replace('.', '_', $name));
        return self::$data[$name] ?? $default;
    }

    /**
     * 设置环境变量值
     *
     * @param string|array $env 环境变量
     * @param mixed $value 值
     * @return void
     */
    public static function set($env, $value = null): void {
        if (is_array($env)) {
            $env = array_change_key_case($env, CASE_UPPER);
            foreach ($env as $key => $val) {
                if (is_array($val)) {
                    foreach ($val as $k => $v) {
                        self::$data[$key . '_' . strtoupper($k)] = $v;
                    }
                } else {
                    self::$data[$key] = $val;
                }
            }
        } else {
            $name = strtoupper(str_replace('.', '_', $env));
            self::$data[$name] = $value;
        }
    }
}