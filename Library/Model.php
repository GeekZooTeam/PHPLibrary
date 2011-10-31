<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */

// helper function
function _model($name = null) {
    static $models = array();

    if (!isset($name)) {
        $name = '__null__';
    }

    if (!isset($models[$name])) {
        $file = Model::$dir."/$name.php";
        $t = explode('/', $name);
        $table_name = end($t);

        if (file_exists($file)) {
            require_once $file;
            $class = $table_name.'_model';
        } else {
            $class = 'Model';
        }
        
        $models[$name] = new $class(Database::$instance);
        
        if ($name !== '__null__') {
            $models[$name]->table = Model::_t($table_name);
        }
    }

    return $models[$name];
}


class Model implements ArrayAccess, Countable
{
    protected $db  = null;
    public static $mc  = null;
    public static $dir = './model';
    public static $dbPrefix = array();

    public $table = '';

    protected function getSql($option)
    {
        $where = array();
        foreach ($option as $key => $val) {
            $where[] = "$key = ?";
        }
        return 'WHERE '.implode(' AND ', $where);
    }

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function read($option)
    {
        $table = $this->table;
        
        if ($table == '') {
            if (!is_string($option)) {
                throw new Exception("no table name, param must be string");
            }
            return $this->db->getRow($option);
        }

        if (is_string($option)) {
            $sql = "SELECT * FROM `$table` $option";

            return $this->db->getRow($sql);
        }

        $sql = "SELECT * FROM `$table` ".$this->getSql($option);

        return $this->db->getRow($sql, array_values($option));
    }

    public function create($option)
    {
        $table = $this->table;

        if ($table == '') {
            if (!is_string($option)) {
                throw new Exception("no table name, param must be string");
            }

            $result = $this->db->exec($option);
            $id = $this->db->lastInsertId();

            return $id;
        }

        $sql = "INSERT INTO `$table` (".implode(', ', array_keys($option)).') VALUES ('.implode(', ', array_fill(0, count($option), '?')) . ')';

        $result = $this->db->exec($sql, array_values($option));
        $id = $this->db->lastInsertId();

        return $id;
    }

    public function getDataBase()
    {
        return $this->db;
    }

    public function update($option, $array = array())
    {
        $table = $this->table;

        if ($table == '') {
            if (!is_string($option)) {
                throw new Exception("no table name, param must be string");
            }
            return $this->db->exec($option);
        }

        if (is_string($option)) {
            $sql = "UPDATE `$table` $option";

            return $this->db->exec($sql);
        }
    
        $set = array();
        foreach ($array as $key => $val) {
            $set[] = "$key = ?";
        }

        $param = array_merge(array_values($array), array_values($option));

        $sql = "UPDATE `$table` SET ".implode(',', $set).' '.$this->getSql($option);
        
        return $this->db->exec($sql, $param);
    }

    public function delete($option)
    {
        $table = $this->table;

        if ($table == '') {
            if (!is_string($option)) {
                throw new Exception("no table name, param must be string");
            }
            return $this->db->exec($option);
        }

        if (is_string($option)) {
            $sql = "DELETE FROM `$table` $option";
            

            return $this->db->exec($sql);
        }

        $sql = "DELETE FROM `$table` ".$this->getSql($option);

        return $this->db->exec($sql, array_values($option));
    }

    public function getList($option  = '', $sqladd = '', $pager = null)
    {
        $table = $this->table;

        if ($table == '') {
            if (!is_string($option)) {
                throw new Exception("no table name, param must be string");
            }
            if (!$sqladd) {
                return $this->db->getAll($option);
            }
            $pager = $sqladd;
            $limit = $pager->setPage()->getLimit();
            
            $option = str_replace('SELECT', 'SELECT SQL_CALC_FOUND_ROWS', $option)." $limit";

            $data = $this->db->getAll($option);
            $count = $this->db->getOne("SELECT FOUND_ROWS()");
            $pager->generate($count);
            return array(
                'data' => $data,
                'pager' => $pager
            );
        }

        if (is_string($option)) {
            $pager = $sqladd;
            if ($pager) {
                $limit = $pager->setPage()->getLimit();
                $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `$table` $option $limit";
                $data = $this->db->getAll($sql);
                $count = $this->db->getOne("SELECT FOUND_ROWS()");
                $pager->generate($count);
                return array(
                    'data' => $data,
                    'pager' => $pager
                );
            } else {
                $sql = "SELECT * FROM `$table` $option";

                return $this->db->getAll($sql);
            }
        }
    
        if (!isset($pager) && !is_string($sqladd)) {
            $pager = $sqladd;
            $limit = $pager->setPage()->getLimit();
            $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `$table` ".$this->getSql($option)." $limit";
            $data = $this->db->getAll($sql, array_values($option));
            $count = $this->db->getOne("SELECT FOUND_ROWS()");
            $pager->generate($count);
            return array(
                'data' => $data,
                'pager' => $pager
            );
        } elseif (isset($pager)) {
            $limit = $pager->setPage()->getLimit();
            $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `$table` ".$this->getSql($option)." $sqladd $limit";
            $data = $this->db->getAll($sql, array_values($option));
            $count = $this->db->getOne("SELECT FOUND_ROWS()");
            $pager->generate($count);
            return array(
                'data' => $data,
                'pager' => $pager
            );
        } else {
            $sql = "SELECT * FROM `$table` ".$this->getSql($option)." $sqladd";

            return $this->db->getAll($sql, array_values($option));
        }
    }

    public function getCount($option = array())
    {
        $table = $this->table;

        if ($table == '') {
            if (!is_string($option)) {
                throw new Exception("no table name, param must be string");
            }
            return $this->db->getOne($option);
        }

        if (is_string($option)) {
            $sql = "SELECT COUNT(*) FROM `$table` $option";

            return $this->db->getOne($sql);
        }

        if ($option) {
            $sql = "SELECT COUNT(*) FROM `$table` ".$this->getSql($option);

            return $this->db->getOne($sql, array_values($option));
        }
        $sql = "SELECT COUNT(*) FROM `$table`";

        return $this->db->getOne($sql);
    }

    public static function _t($table_name)
    {
        foreach (self::$dbPrefix as $key => $val) {
            if ($val == '*') {
                return $key.$table_name;
            }
            if (in_array($table_name, $val)) {
                return $key.$table_name;
            }
        }
        return $table_name;
    }

    // interface

    public function count()
    {
        return $this->getCount();
    }

    public function offsetGet($key)
    {
        $result = $this->read(array('id'=>$key));

        return $result;
    }

    public function offsetExists($key)
    {
        if ($result = $this->read(array('id'=>$key))) {
            return true;
        }

        return false;
    }

    public function offsetSet($key, $val)
    {
        return $this->update(array('id'=>$key), $val);
    }
    // delete
    public function offsetUnset($key)
    {
        return $this->delete(array('id'=>$key));
    }
}

?>