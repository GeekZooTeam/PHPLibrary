<?php

return array(

'controllerDir' => '',

'modelDir'=>'',

'database' => array(
    'connection' => array('pdo', 'mysql:host=localhost;dbname=test', 'root', 'root'), //'mysqli://root:root@localhost/test',
    'initialization' => array(
        'SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary',
        'SET sql_mode=``'
    ),
    'tablePreFix' => ''
),

'classMap' => array(
    'Controller' => ROOT_PATH.'/abstracts/Controller.php',
    'frontController' => ROOT_PATH.'/abstracts/frontController.php',
    'adminController' => ROOT_PATH.'/abstracts/adminController.php',
    'apiBase' => ROOT_PATH.'/abstracts/apiBase.php',
    'apiController' => ROOT_PATH.'/abstracts/apiController.php'
),


);