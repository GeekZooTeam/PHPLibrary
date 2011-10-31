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

    /**
     * set a config value
     *
     * @param mix $key 
     * @param mix $value 
     * @param string $action 
     * @return bool
     */
    public static function set($key, $value = '')
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }

        foreach ($key as $k => $v) {
            self::$array[$k] = $v;
        }

        return true;
    }

    /**
     * get a config value
     *
     * @param string $key
     * @return mix
     */
    public static function get($key)
    {
        if (func_num_args() > 1) {
            $key = func_get_args();
            $out = array();
            foreach ($key as $key => $val) {
                $out[] = @self::$array[$key];
            }

            return $out;
        }

        return @self::$array[$key];
    }
}


?>