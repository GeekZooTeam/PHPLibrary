<?php
return array(


'controllerDir' => '',

'modelDir'=>'',

'database' => array(
    'connection' => 'mysqli://root:Shouke*liutie@localhost/btv',
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

'config' => array(
    'donate_url' => "http://www.brtv.com/xxx/:id",
    'dream_url'  => "http://www.brtv.com/xxx",
    'push_enabled' => 1
),

'uploadPath' => ROOT_PATH.'/upload_files',

'btvApi' => array(
    'url' => 'http://sns.brtn.cn/rest/%s?token=%s',
    'token' => '123456'
)





);