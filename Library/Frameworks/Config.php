<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */

class Config
{
    private static $array = array();
    private static $init = false;

    private static function init()
    {
        if (self::$init) {
            return true;
        }

        $path = ROOT_PATH.'/config';
        $iterator = new DirectoryIterator($path);
        foreach ($iterator as $file) {
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }

            $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

            if ($extension != 'php') {
                continue;
            }

            self::$array[$file->getBasename('.php')] = include $file->getPathname();
        }

        return self::$init = true;
    }

    public static function get($name)
    {
        self::init();

        $name = explode('.', $name);
        
        $array = isset(self::$array[$name[0]]) ? self::$array[$name[0]] : array();
        
        if (!isset($name[1])) {
            return $array;
        }

        return isset($array[$name[1]]) ? $array[$name[1]] : null;
    }

    public static function set($name, $value)
    {
        self::init();

        $name = explode('.', $name);
        
        if (!isset($name[1])) {
            self::$array[$name[0]] = $value;
        }

        self::$array[$name[0]][$name[1]] = $value;
    }
}