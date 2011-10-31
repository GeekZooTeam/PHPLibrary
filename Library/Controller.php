<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */

abstract class Controller
{
    public static $format = null;
    private static $rules = array();

    /**
     * add a url rule
     *
     * @param string $rule 
     * @param string $file 
     * @param string $action 
     * @return void
     */
    public static function addRule($rule, $file = 'index', $action = 'index')
    {
        self::$rules[$rule] = array($file, $action);
    }

    /**
     * dispatch url function
     *
     * @param string $url 
     * @param string $path 
     * @param string $delimiter 
     * @return mix
     */
    public static function dispatch($url = null, $path = null, $delimiter = '/')
    {
        isset($url)  || $url = @$_GET['url'];
        isset($path) || $path = dirname(__FILE__).'/controller';
        $t_path = $path;
        
        $url = trim($url, ' '.$delimiter);
        
        foreach (self::$rules as $key => $val) {
            if (preg_match("@$key@", $url, $tmp)) {
                array_shift($tmp);
                $file   = $path.'/'.$val[0];
                $action = $val[1];
                break;
            }
        }

        if (empty($file)) {
            // trim the url extention (xxx/xxx.html or yyy/yyy.asp or any extention)
            if (($pos = strrpos($url, '.')) !== false) {
                self::$format = substr($url, $pos+1);
                $url = substr($url, 0, $pos);
            }

            $tmp = array_filter(explode($delimiter, $url));
            $count = count($tmp);
            for ($i = 0; $i < $count; $i++) {
                if (!is_dir($path.'/'.$tmp[$i])) {
                    break;
                }
                $path .= '/'.$tmp[$i];
            }
            $i && $tmp = array_slice($tmp, $i);


            $controller = array_shift($tmp);
            $action = array_shift($tmp);

            !$controller && $controller = 'index';
            !$action && $action = 'index';
            $file = $path.'/'.$controller.'.php';
        }

        if (!file_exists($file)) {
            $action = $controller;
            $controller = 'index';
            $file = $t_path.'/'.$controller.'.php';
            //throw new Exception("Controller not exists: $controller", 404);
        }

        include $file;
        $class = new Action($controller, $action, $tmp);

        return call_user_func_array(array($class, $action), $tmp);
    }
}

?>