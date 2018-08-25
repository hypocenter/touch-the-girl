<?php
/**
 * Created by PhpStorm.
 * User: hypo
 * Date: 2018/8/25
 * Time: 23:22
 */

namespace Ttg;


class Config extends Component
{
    private $configs = [];
    private $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function boot()
    {
        $dir = opendir($this->dir);
        while ($file = readdir($dir)) {
            if ($file == '..' || $file == '.') {
                continue;
            }

            $info = pathinfo($file);
            $name = $info['filename'] ?? null;
            $ext = $info['extension'] ?? null;

            if (!$name || !$ext || $ext !== 'php') {
                continue;
            }

            $conf = require $this->dir . DIRECTORY_SEPARATOR . $file;
            $this->configs[$name] = $conf;
        }
    }

    public function get($path = '', $defualt = null)
    {
        $path = trim($path);

        if (empty($path)) {
            return $this->configs;
        }

        $path = explode('.', $path);
        $conf = $this->configs;
        foreach ($path as $n) {
            if (isset($conf[$n])) {
                $conf = $conf[$n];
            } else {
                $conf = null;
            }
        }

        return $conf ? $conf : $defualt;
    }

    public function set($path, $value)
    {
        $path = explode('.', $path);
        $conf = &$this->configs;
        foreach ($path as $n) {
            if (!array_key_exists($n, $conf)) {
                $conf[$n] = [];
            }
            $conf = &$conf[$n];
        }

        $conf = $value;
    }
}