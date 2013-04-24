<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
*/

defined('ROOT_PATH') || define('ROOT_PATH', getcwd());
define('LIB_PATH', dirname(__FILE__));

// if cant get date.timezone set the default timezone
if (!ini_get('date.timezone')) {
    date_default_timezone_set('Asia/Chongqing');
}

if (version_compare(PHP_VERSION, '5.3.0', '<')) { // below 5.3
    $loader = include_once LIB_PATH . '/autoload.php';
} else {
    $loader = include_once LIB_PATH . '/vendor/autoload.php';
}

$classMap = array();
foreach (glob(LIB_PATH . "/Frameworks/*.php") as $value) {
    $class = basename($value, '.php');
    $classMap[$class] = $value;
}

$loader->addClassMap($classMap);


if (PHP_SAPI != 'cli') {

    if (!defined('HTTPS')) {
        if (isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on')) {
            define('HTTPS', 1);
        } else {
            define('HTTPS', 0);
        }
    }

    if (!isset($_SERVER['HTTP_HOST'])) {
        $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'];
    }

    if (strpos($_SERVER['HTTP_HOST'], ':')) {
        $_SERVER['HTTP_HOST'] = strtok($_SERVER['HTTP_HOST'], ':');
    }

    // auto check subdir
    $subDir = '';
    if (isset($_SERVER['PHP_SELF']) && strpos($_SERVER['PHP_SELF'], 'index.php')) {
        $subDir = dirname($_SERVER['PHP_SELF']);
    } elseif (isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT']) === 0) {
        $subDir = dirname(substr($_SERVER['SCRIPT_FILENAME'], strlen($_SERVER['DOCUMENT_ROOT'])));
    }
    $subDir = rtrim($subDir, '/');

    if (!defined('SITE_URL')) {
        $site_url = HTTPS ? 'https://' : 'http://';
        $site_url .= $_SERVER['HTTP_HOST'];

        // echo $site_url;exit;
        if (isset($_SERVER['SERVER_PORT'])) {
            if ((HTTPS && $_SERVER['SERVER_PORT'] != 443) || ($_SERVER['SERVER_PORT'] != 80)) {
                $site_url .= ':' . $_SERVER['SERVER_PORT'];
            }
        }
        $subDir && $site_url .= $subDir;

        define('SITE_URL', $site_url);
    }
    
    // fix SCRIPT_URL
    if (empty($_SERVER['SCRIPT_URL'])) {
        if (!empty($_SERVER['REDIRECT_URL'])) {
            $_SERVER['SCRIPT_URL'] = substr($_SERVER['REDIRECT_URL'], strlen($subDir)+1);
        } elseif (!empty($_SERVER['REQUEST_URI'])) {
            $p = parse_url($_SERVER['REQUEST_URI']);
            $_SERVER['SCRIPT_URL'] = substr($p['path'], strlen($subDir)+1);
        }
    }

    // it seems as 'php bug in fast-cgi no $_GET'
    // if (strpos($_SERVER['REQUEST_URI'], '?') !== false && empty($_GET)) {
    //     $p = parse_url($_SERVER['REQUEST_URI']);
    //     parse_str($p['query'], $_GET);
    // }

    // if magic_quotes_sybase is ON then do this:
    if (get_magic_quotes_gpc()) {
        $_GET    = stripslashes_recursive($_GET);
        $_POST   = stripslashes_recursive($_POST);
        $_COOKIE = stripslashes_recursive($_COOKIE);
    }
}

return $loader;

function getMacAdress() {
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

function stripslashes_recursive($array) {
    $array = is_array($array) ? array_map(__FUNCTION__, $array) : stripslashes($array);

    return $array;
}

/**
 * modelclass::getInstance() 别名
 *
 * @param $model name
 * @return Model Class
 */
function _model($model) {
    $class= $model . 'Model';

    return $class::getInstance();
}

/**
 * 递归合并数组，并对没有下标的数组进行替换而不是相加操作（区别于array_merge_recursive）
 * @param array $a 原数组
 * @param array $b 追加，替换数组
 * @return array 合并后的数组
 * @author xuanyan
 */
function mergeRecursive($a, $b)
{
    foreach ($b as $key => $value) {
        // 没有key替换
        if (!isset($a[$key])) {
            $a[$key] = $value;
            continue;
        }
        // 原数组当前key不是数组，或者需替换数组当前值不是数组
        if (!is_array($value) || !is_array($a[$key])) {
            $a[$key] = $value;
            continue;
        }
        // 原数组和替换数组，当前都为数组，进行递归替换
        $a[$key] = mergeRecursive($a[$key], $value);
    }

    return $a;
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

function getValueByDefault($value, $default) {
    if (!is_array($value)) {

        $whiteList = array();
        if (is_array($default)) {
            $whiteList = $default;
        }
        $default = $default[0];

        if (is_string($default)) {
            $value = trim($value);
        } elseif (is_int($default)) {
            $value = intval($value);
        } else {
            $value = floatval($value);
        }

        if ($whiteList && !in_array($value, $whiteList)) {
            $value = $default;
        }

    } else {
        foreach ($value as $key => $val) {
            isset($default[$key]) || $default[$key] = '';
            $value[$key] = getValueByDefault($value[$key], $default[$key]);
        }
        $value = array_merge($value, array_diff_key($default, $value));
    }

    return $value;
}

function _GET($key = '', $default = '') {
    if (empty($key)) {
        return $_GET;
    }

    if (!isset($_GET[$key])) {
        $_GET[$key] = '';
    }
    $value = getValueByDefault($_GET[$key], $default);

    return $value;
}

function _POST($key = '', $default = '') {
    if (empty($key)) {
        return $_POST;
    }

    if (!isset($_POST[$key])) {
        $_POST[$key] = '';
    }

    $value = getValueByDefault($_POST[$key], $default);

    return $value;
}

function _ARGV($key = '', $default = '') {
    if (empty($GLOBALS['argv']) || !is_array($GLOBALS['argv'])) {
        $GLOBALS['argv'] = array();
    }

    $result = array();
    $last_arg = null;
    foreach ($GLOBALS['argv'] as $val) {
        $pre = substr($val, 0, 2);
        if ($pre == '--') {
            $parts = explode("=", substr($val, 2), 2);
            if (isset($parts[1])) {
                $result[$parts[0]] = $parts[1];
            } else {
                $result[$parts[0]] = true;
            }
        } elseif ($pre{0} == '-') {
            $string = substr($val, 1);
            $len = strlen($string);
            for ($i = 0; $i < $len; $i++) {
                $key = $string[$i];
                $result[$key] = true;
            }
            $last_arg = $key;
        } elseif ($last_arg !== null) {
            $result[$last_arg] = $val;
            $last_arg = null;
        }
    }

    if (empty($key)) {
        return $result;
    }

    if (!isset($result[$key])) {
        $result[$key] = '';
    }

    return getValueByDefault($result[$key], $default);
}