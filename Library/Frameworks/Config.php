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

    public static $db = null;

    // private static function init()
    // {
    //     if (self::$init) {
    //         return true;
    //     }
    // 
    //     $path = ROOT_PATH.'/config';
    //     $iterator = new DirectoryIterator($path);
    //     foreach ($iterator as $file) {
    //         if ($file->isDot() || !$file->isFile()) {
    //             continue;
    //         }
    // 
    //         $extension = pathinfo($file->getFilename(), PATHINFO_EXTENSION);
    // 
    //         if ($extension != 'php') {
    //             continue;
    //         }
    // 
    //         self::$array[$file->getBasename('.php')] = include $file->getPathname();
    //     }
    // 
    //     return self::$init = true;
    // }
    private static function loadConfig($name)
    {
        if (isset(self::$array[$name])) {
            return true;
        }

        $fileName = ROOT_PATH."/config/{$name}.php";

        if (file_exists($fileName)) {
            self::$array[$name] = include $fileName;
        }

        if (self::$db !== null) {
            $data = self::$db->getAll("SELECT * FROM {{config}}");

            foreach ($data as $val) {
                $config = isset(self::$array[$val['key']]) ? self::$array[$val['key']] : array();
                $val['data'] = json_decode($val['data'], true);
                if (is_array($val['data'])) {
                    self::$array[$val['key']] = array_merge($config, $val['data']);
                } else {
                    self::$array[$val['key']] = $val['data'];
                }
            }
        }
    }

    public static function get($name)
    {
        $name = explode('.', $name);

        self::loadConfig($name[0]);

        $array = isset(self::$array[$name[0]]) ? self::$array[$name[0]] : array();
        
        if (!isset($name[1])) {
            return $array;
        }

        return isset($array[$name[1]]) ? $array[$name[1]] : null;
    }

    public static function set($name, $value)
    {
        $name = explode('.', $name);

        self::loadConfig($name[0]);

        if (!isset($name[1])) {
            self::$array[$name[0]] = $value;
        } else {
            if (!is_array(self::$array[$name[0]])) {
                self::$array[$name[0]] = array();
            }
            self::$array[$name[0]][$name[1]] = $value;
        }

        if (self::$db !== null) {
            $data = json_encode(self::$array[$name[0]]);
            $sql = "INSERT INTO {{config}} VALUES(?, ?) ON DUPLICATE KEY UPDATE data = ?";
            self::$db->exec($sql, $name[0], $data, $data);
        }
    }
}