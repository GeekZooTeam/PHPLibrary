<?php

/*
 * This file is part of the Geek-Zoo Projects.
 *
 * @copyright (c) 2010 Geek-Zoo Projects More info http://www.geek-zoo.com
 * @license http://opensource.org/licenses/gpl-2.0.php The GNU General Public License
 * @author xuanyan <xuanyan@geek-zoo.com>
 *
 */

class Model extends Singleton
{
    protected $db  = null;

    public $table = '';

    protected $field = array();

    protected $pri = '';

    public function __construct()
    {
        $dbConfig = Config::getInstance()->get('database');

        $this->db = Database::connect($dbConfig['connection']);
        $this->db->setConfig('initialization', $dbConfig['initialization']);
        $this->db->setConfig('tablePreFix', $dbConfig['tablePreFix']);

        if (empty($this->table)) {
            $thisClass = get_class($this);
            $this->table = '{{' . substr($thisClass, 0, -5) . '}}';
        }

        $keys = $this->db->getAll("DESCRIBE `$this->table`");
        foreach ($keys as $val) {
            $this->field[] = $val['Field'];
            if ($val['Key'] == 'PRI') {
                $this->pri = $val['Field'];
            }
        }
    }

    protected function getSql($option)
    {
        $where = array();
        foreach ($option as $key => $val) {
            $where[] = "`$key` = ?";
        }
        return 'WHERE '.implode(' AND ', $where);
    }

    public function read()
    {
        $table = $this->table;

        $params = func_get_args();
        $sql = array_shift($params);

        // 主键 id 特殊处理
        if (!is_array($sql)) {
            if (strlen(intval($sql)) == strlen($sql)) {
                $sql = array($this->pri => $sql);
            }
        }

        if (is_string($sql)) {
            $sql = "SELECT * FROM `$table` $sql";
            if ($params) {
                if (is_array($params[0])) {
                    $params = $params[0];
                }

                return $this->db->getRow($sql, $params);
            }

            return $this->db->getRow($sql);
        }

        $params = array_values($sql);

        $sql = "SELECT * FROM `$table` ".$this->getSql($sql);

        return $this->db->getRow($sql, $params);
    }

    public function create()
    {
        $table = $this->table;

        $params = func_get_args();
        $sql = array_shift($params);

        $sql = $this->beforeCreate($sql);

        $params = array_values($sql);

        $sql = "INSERT INTO `$table` (".implode(', ', array_keys($sql)).') VALUES ('.implode(', ', array_fill(0, count($sql), '?')) . ')';

        $result = $this->db->exec($sql, $params);

        $id = $this->db->lastInsertId();

        // 没有自增id
        if (!$id) {
            return $result;
        }

        $new = $this->read($id);

        $this->afterCreate($new);

        return $id;
    }

    public function update()
    {
        $table = $this->table;

        $params = func_get_args();
        $sql = array_shift($params);

        // 主键 id 特殊处理
        if (!is_array($sql)) {
            if (strlen(intval($sql)) == strlen($sql)) {
                $sql = array($this->pri => $sql);
            }
        }

        if (is_string($sql)) {

            if ($params) {
                if (is_array($params[0])) {
                    $params = $params[0];
                }
            }

            if (strpos($sql, 'WHERE') === false) {
                $select = "SELECT * FROM `$table`";
                $result = $this->db->getAll($select);
                $set = $sql;
            } else {
                list($set, $where) = explode('WHERE', $sql);
                $select = "SELECT * FROM `$table` WHERE $where";
                $c = substr_count($select, '?');
                $select_param = array();
                while ($c) {
                    $select_param[] = array_pop($params);
                    $c--;
                }
                $select_param = array_reverse($select_param);
                if ($select_param) {
                    $result = $this->db->getAll($select, $select_param);
                } else {
                    $result = $this->db->getAll($select);
                }
                $array = array_merge(array($set), $params);
            }

        } else {
            $select = "SELECT * FROM `$table` ".$this->getSql($sql);
            $result = $this->db->getAll($select, array_values($sql));
            $array = array_shift($params);
        }

        foreach ($result as $val) {
            $array = $this->beforeUpdate($array, $val);

            if (isset($array[0])) {
                $params = $array;
                $set = array_shift($params);
            } else {
                $params = array_values($array);
                $set = array();

                foreach ($array as $kk => $vv) {
                    $set[] = "$kk = ?";
                }

                $set = 'SET ' . implode(',', $set);
            }

            $sql = "UPDATE `$table` $set WHERE {$this->pri} = {$val[$this->pri]}";
            $this->db->exec($sql, $params);
            $this->afterUpdate($array, $val);
        }

        return count($result);
    }

    public function delete()
    {
        $table = $this->table;

        $params = func_get_args();
        $sql = array_shift($params);

        // 主键 id 特殊处理
        if (!is_array($sql)) {
            if (strlen(intval($sql)) == strlen($sql)) {
                $sql = array($this->pri => $sql);
            }
        }

        if (is_string($sql)) {

            if ($params) {
                if (is_array($params[0])) {
                    $params = $params[0];
                }
            }

            $sql = "SELECT * FROM `$table` $sql";
            if (strpos($sql, '?') !== false) {
                $result = $this->db->getAll($sql, $params);
            } else {
                $result = $this->db->getAll($sql);
            }
        } else {
            $params = $sql;
            $sql = "SELECT * FROM `$table` ".$this->getSql($params);

            $result = $this->db->getAll($sql, array_values($params));
        }
        
        foreach ($result as $val) {
            $this->beforeDelete($val);
            $sql = "DELETE FROM `$table` WHERE {$this->pri} = {$val[$this->pri]}";
            $this->db->exec($sql);
            $this->afterDelete($val);
        }

        return count($result);
    }

    public function getList()
    {
        $table = $this->table;
        $pager = null;

        $params = func_get_args();
        $sql = array_shift($params);

        if (!$sql) {
            $sql = '';
        } elseif (is_object($sql)) {
            $pager = $sql;
            $sql = '';
        } elseif (isset($params[0]) && is_object($params[0])) {
            $pager = array_shift($params);
        }

        if (is_string($sql)) {

            if ($params) {
                if (is_array($params[0])) {
                    $params = $params[0];
                }
            }

        } else {
            $params = $sql;
            $sql = $this->getSql($sql);
        }
        
        if ($pager) {
            $limit = $pager->setPage()->getLimit();
            $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM `$table` $sql $limit";
        } else {
            $sql = "SELECT * FROM `$table` $sql";
        }

        if ($params) {
            $result = $this->db->getAll($sql, $params);
        } else {
            $result = $this->db->getAll($sql);
        }
        
        if (!$pager) {
            return $result;
        }
        $count = $this->db->getOne("SELECT FOUND_ROWS()");
        $pager->generate($count);
        return array(
            'data' => $result,
            'pager' => $pager
        );
    }

    public function getSum()
    {
        $table = $this->table;

        $params = func_get_args();
        $column = array_shift($params);
        $sql = array_shift($params);
        $sql || $sql = '';
        if (is_string($sql)) {

            if ($params) {
                if (is_array($params[0])) {
                    $params = $params[0];
                }
            }

            $sql = "SELECT SUM($column) FROM `$table` $sql";
            if (strpos($sql, '?') !== false) {
                $result = $this->db->getOne($sql, $params);
            } else {
                $result = $this->db->getOne($sql);
            }
        } else {
            $params = $sql;
            $sql = "SELECT SUM($column) FROM `$table` ".$this->getSql($params);

            $result =  $this->db->getOne($sql, array_values($params));
        }

        return intval($result);
    }

    public function getCount()
    {
        $table = $this->table;
        $params = func_get_args();
        $sql = array_shift($params);
        $sql || $sql = '';
        
        if (is_string($sql)) {

            if ($params) {
                if (is_array($params[0])) {
                    $params = $params[0];
                }
            }

            $sql = "SELECT COUNT(*) FROM `$table` $sql";
            if (strpos($sql, '?') !== false) {
                return $this->db->getOne($sql, $params);
            }

            return $this->db->getOne($sql);
        }

        $params = $sql;
        $sql = "SELECT COUNT(*) FROM `$table` ".$this->getSql($params);

        return $this->db->getOne($sql, array_values($params));
    }

    protected function beforeCreate($new)
    {
        return $new;
    }

    protected function afterCreate($new)
    {

    }

    protected function beforeUpdate($new, $old)
    {
        return $new;
    }

    protected function afterUpdate($new, $old)
    {
        
    }
    
    
    protected function beforeDelete($old)
    {
        
    }

    protected function afterDelete($old)
    {
        
    }
}