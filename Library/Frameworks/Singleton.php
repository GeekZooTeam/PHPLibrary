<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */


class Singleton
{
    protected static $instance = array();

    public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new static();
        }

        return self::$instance[$class];
        // if (!static::$instance instanceof static) {
        //     
        //     echo get_called_class();
        //     echo '<br>';
        //     static::$instance = new static();
        // }
        // 
        // return static::$instance;
    }
}