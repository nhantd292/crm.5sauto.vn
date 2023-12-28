<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
return array(
    'db' => array(
        'adapters' => array(
            'dbConfig' => array(
                'driver'   => 'Pdo_Mysql',
                'database' => 'qclothes',
                'username' => 'root',
                'password' => '',
                'hostname' => 'localhost',
                'port'     => '',
                'charset'  => 'utf8'
            ),
            'dbNotify' => array(
                'driver'   => 'Pdo_Mysql',
                'database' => 'forewinv_crm_notify',
                'username' => 'root',
                'password' => '',
                'hostname' => 'localhost',
                'port'     => '',
                'charset'  => 'utf8'
            ),
        )
    ),
//    'db' => array(
//        'adapters' => array(
//            'dbConfig' => array(
//                'driver'   => 'Pdo_Mysql',
//                'database' => 'qclot425_db',
//                'username' => 'qclot425_user',
//                'password' => 'qclothes@123',
//                'hostname' => 'localhost',
//                'port'     => '',
//                'charset'  => 'utf8'
//            ),
//            'dbNotify' => array(
//                'driver'   => 'Pdo_Mysql',
//                'database' => 'qclot425_notifi',
//                'username' => 'qclot425_user',
//                'password' => 'qclothes@123',
//                'hostname' => 'localhost',
//                'port'     => '',
//                'charset'  => 'utf8'
//            ),
//        )
//    ),

    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Db\Adapter\AdapterAbstractServiceFactory'
        )
    )
);
?>