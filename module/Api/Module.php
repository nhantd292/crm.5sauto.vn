<?php

namespace Api;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Db\TableGateway\TableGateway;

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
            	'Api\Model\SaleFormTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_FORM, $adapter, null);
            		return new \Api\Model\SaleFormTable($tableGateway);
            	},
            	'Api\Model\SaleFormDataTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_FORM_DATA, $adapter, null);
            		return new \Api\Model\SaleFormDataTable($tableGateway);
            	},
            	'Api\Model\SaleCompanyBranchTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_COMPANY_BRANCH, $adapter, null);
            		return new \Api\Model\SaleCompanyBranchTable($tableGateway);
            	},
            	'Api\Model\SaleDocumentTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_DOCUMENT, $adapter, null);
            		return new \Api\Model\SaleDocumentTable($tableGateway);
            	},
            	'Api\Model\SaleContactTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_CONTACT, $adapter, null);
            		return new \Api\Model\SaleContactTable($tableGateway);
            	},
            	'Api\Model\SaleContractTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_CONTRACT, $adapter, null);
            		return new \Api\Model\SaleContractTable($tableGateway);
            	},
            	'Api\Model\SaleBillTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_BILL, $adapter, null);
            		return new \Api\Model\SaleBillTable($tableGateway);
            	},
            	'Api\Model\OfflineTrainingClassTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_TRAINING_CLASS, $adapter, null);
            		return new \Api\Model\OfflineTrainingClassTable($tableGateway);
            	},
            	'Api\Model\OfflineProductTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_PRODUCT, $adapter, null);
            		return new \Api\Model\OfflineProductTable($tableGateway);
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
