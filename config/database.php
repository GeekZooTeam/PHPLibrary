<?php

$config = array(

'connection' => array('pdo', 'mysql:host=localhost;dbname=test', 'root', 'root'), //'mysqli://root:root@localhost/test',
'initialization' => array(
    'SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary',
    'SET sql_mode=``'
),
'tablePreFix' => ''

);

return $config;