<?php

namespace Notifycation;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Db\TableGateway\TableGateway;

class Module {
	public function onBootstrap(MvcEvent $e) {
		$eventManager        = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$adapter = $e->getApplication()->getServiceManager()->get('dbNotify');
		GlobalAdapterFeature::setStaticAdapter($adapter);
	}
	
    public function getConfig() {
        return array_merge(
            include __DIR__ . '/config/module.config.php',
            include __DIR__ . '/config/router.config.php'
        );
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            )
        );
    }
    
    public function getServiceConfig(){
        return array(
            'factories'	=> array(
                'Notifycation\Model\NotifyTable'	=> function ($sm) {
                	$adapter = $sm->get('dbNotify');
                	$tableGateway = new TableGateway(TABLE_NOTIFY, $adapter, null);
                	return new \Notifycation\Model\NotifyTable($tableGateway);
                },
            ),
        );
    }

    public function getFormElementConfig() {
        return array(
            'factories' => array(
            )
        );
    }
}
