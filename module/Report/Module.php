<?php

namespace Report;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;

class Module {
	public function onBootstrap(MvcEvent $e) {
		$eventManager        = $e->getApplication()->getEventManager();
		$moduleRouteListener = new ModuleRouteListener();
		$moduleRouteListener->attach($eventManager);
		
		$adapter = $e->getApplication()->getServiceManager()->get('dbConfig');
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
