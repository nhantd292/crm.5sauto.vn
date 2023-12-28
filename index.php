<?php
include_once 'define.php';
include_once PATH_LIBRARY . '/Zend/Loader/AutoloaderFactory.php';
header("Access-Control-Allow-Origin: *");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR);
Zend\Loader\AutoloaderFactory::factory(array(
    'Zend\Loader\StandardAutoloader' => array(
        'autoregister_zf' => true,
        'namespaces'	  => array(
            'ZendX'	            => PATH_LIBRARY . '/ZendX',
            'Block'	            => PATH_APPLICATION . '/block',
            'PHPImageWorkshop'  => PATH_VENDOR . '/PHPImageWorkshop'
        ),
        'prefixes'		  => array(
            'HTMLPurifier' => PATH_VENDOR . '/HTMLPurifier'
        )
    )
));

if (!class_exists('Zend\Loader\AutoloaderFactory')) {
    throw new RuntimeException('AutoloaderFactory is not exist!');
}

Zend\Mvc\Application::init(require_once 'config/application.config.php')->run();

/* $gid = new \ZendX\Functions\Gid();
echo $gid->getId(); */