<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */

class Config extends Singleton
{
    private static $array = array();

    function __construct()
    {
        if (file_exists(ROOT_PATH.'/config.php')) {
            self::$array = require_once ROOT_PATH.'/config.php';
        }
        // todo 远程提取
    }

    function get($key)
    {
        return isset(self::$array[$key]) ? self::$array[$key] : null;
    }
}


?>