<?php

class testModel extends Model
{
    function afterCreate($new)
    {
        //print_r($new);
    }

    function beforeCreate($new)
    {
        return $new;
    }

    function beforeUpdate($new, $old)
    {
        //print_r($new);
        return $new;
    }

    function afterUpdate($new, $old)
    {
        // print_r($this->db);exit;
        // print_r($new);
        // print_r($old);
    }

    function afterDelete($old)
    {
        //print_r($old);
    }
}

?>