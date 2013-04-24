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

    protected function getSql($option)
    {
        $where = array();
        foreach ($option as $key => $val) {
            $where[] = "$key = ?";
        }
        return 'WHERE '.implode(' AND ', $where);
    }

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

    public function update($option, $array)
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
    
    
    public function getSum($column, $option = array())
    {
        $table = $this->table;

        if ($table == '') {
            if (!is_string($option)) {
                throw new Exception("no table name, param must be string");
            }
            return $this->db->getOne($option);
        }

        if (is_string($option)) {
            $sql = "SELECT SUM($column) FROM `$table` $option";

            return $this->db->getOne($sql);
        }

        if ($option) {
            $sql = "SELECT SUM($column) FROM `$table` ".$this->getSql($option);

            return $this->db->getOne($sql, array_values($option));
        }
        $sql = "SELECT SUM($column) FROM `$table`";

        return $this->db->getOne($sql);
    }
}
