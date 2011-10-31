<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */

Initialization::start();

final class Initialization
{
    private static $basedir = null;
    private static $start_time = null;

    public static function start()
    {
        self::$basedir = dirname(__FILE__);
        self::$start_time = microtime(true);
        // set default timezone
        date_default_timezone_set('PRC');
        // register the autoload function
        spl_autoload_register(array(__CLASS__, '__autoload'));
        // if magic_quotes_sybase is ON then do this:
        if (get_magic_quotes_gpc()) {
            $_GET    = self::stripslashes_recursive($_GET);
            $_POST   = self::stripslashes_recursive($_POST);
            $_COOKIE = self::stripslashes_recursive($_COOKIE);
        }
    }

    public static function getRuntime()
    {
        return microtime(true) - self::$start_time;
    }

    private static function __autoload($classname)
    {
        switch  ($classname) {
            case 'Database':
                require_once self::$basedir.'/3rd/SuperNuts/'.$classname.'.php';
                break;
            case 'Smarty':
                require_once self::$basedir.'/3rd/Smarty3/Smarty.class.php';
                break;
            case 'getid3':
                require_once self::$basedir . '/3rd/getid3/getid3.php';
                break;
            case 'PHPMailer':
                require_once self::$basedir . '/3rd/PHPMailer/class.phpmailer.php';
                break;
            default:
                $file = self::$basedir.'/'.$classname.'.php';
                if (file_exists($file)) {
                    require_once $file;
                }
        }
    }

    private static function stripslashes_recursive($array)
    {
        $array = is_array($array) ? array_map(array(__CLASS__, 'stripslashes_recursive'), $array) : stripslashes($array);

        return $array;
    }

    public static function getMacAdress()
    {
        $return_array = array();
        if (DIRECTORY_SEPARATOR == '/') { // linux
            @exec("ifconfig -a", $return_array);
        } else {
            @exec("ipconfig /all", $return_array);

            if (!$return_array) {
                $ipconfig = $_SERVER["WINDIR"]."\system32\ipconfig.exe";
                if (file_exists($ipconfig)) {
                    @exec($ipconfig." /all", $return_array);
                } else {
                    @exec($_SERVER["WINDIR"]."\system\ipconfig.exe /all", $return_array);
                }
            }
        }

        foreach ($return_array as $key => $val) {
            if (preg_match('/(?:\w{2}[:-]){5}\w{2}/', $val, $match)) {
                $return_array[$key] = $match[0];
            } else {
                unset($return_array[$key]);
            }
        }

        return array_values($return_array);
    }
}

function getClienip() {
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
         $onlineip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
         $onlineip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
         $onlineip = $_SERVER['REMOTE_ADDR'];
    } else {
        return 'unknown';
    }

    return filter_var($onlineip, FILTER_VALIDATE_IP) !== false ? $onlineip : 'unknown';
}

function _GET($key = '', $default = '') {
    if (empty($key)) {
        return $_GET;
    }
    if (!isset($_GET[$key])) {
        return $default;
    }
    if (is_string($default)) {
        return trim($_GET[$key]);
    }
    if (is_int($default)) {
        return intval($_GET[$key]);
    }
    if (is_array($default)) {
        return (array)$_GET[$key];
    }
    return floatval($_GET[$key]);
}

function _POST($key = '', $default = '') {
    if (empty($key)) {
        return $_POST;
    }
    if (!isset($_POST[$key])) {
        return $default;
    }
    if (is_string($default)) {
        return trim($_POST[$key]);
    }
    if (is_int($default)) {
        return intval($_POST[$key]);
    }
    if (is_array($default)) {
        return (array)$_POST[$key];
    }
    return floatval($_POST[$key]);
}

?>