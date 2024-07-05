<?php

namespace Admin;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\Db\TableGateway\TableGateway;
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
            	'Admin\Model\UserTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_USER, $adapter, null);
            		return new \Admin\Model\UserTable($tableGateway);
            	},
            	'Admin\Model\TeacherTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_USER, $adapter, null);
            		return new \Admin\Model\TeacherTable($tableGateway);
            	},
            	'Admin\Model\CoachTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_USER, $adapter, null);
            		return new \Admin\Model\CoachTable($tableGateway);
            	},
            	'Admin\Model\PermissionTable' => function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_PERMISSION, $adapter, null);
            		return new \Admin\Model\PermissionTable($tableGateway);
            	},
            	'Admin\Model\PermissionListTable'	=> function ($sm) {
            		$adapter = $sm->get('dbConfig');
            		$tableGateway = new TableGateway(TABLE_PERMISSION_LIST, $adapter, null);
            		return new \Admin\Model\PermissionListTable($tableGateway);
            	},
                'Admin\Model\DynamicTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_DYNAMIC, $adapter, null);
                    return new \Admin\Model\DynamicTable($tableGateway);
                },
                'Admin\Model\DocumentTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_DOCUMENT, $adapter, null);
                    return new \Admin\Model\DocumentTable($tableGateway);
                },
                'Admin\Model\NestedTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_NESTED, $adapter, null);
                    return new \Admin\Model\NestedTable($tableGateway);
                },
                'Admin\Model\SettingTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_SETTING, $adapter, null);
                    return new \Admin\Model\SettingTable($tableGateway);
                },
                'Admin\Model\ContactTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_CONTACT, $adapter, null);
                    return new \Admin\Model\ContactTable($tableGateway);
                },
                'Admin\Model\ContractTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_CONTRACT, $adapter, null);
                    return new \Admin\Model\ContractTable($tableGateway);
                },
                'Admin\Model\ContractDetailTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_CONTRACT_DETAIL, $adapter, null);
                    return new \Admin\Model\ContractDetailTable($tableGateway);
                },
                'Admin\Model\ContractFeeTable' => function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_CONTRACT_FEE, $adapter, null);
                    return new \Admin\Model\ContractFeeTable($tableGateway);
                },
                'Admin\Model\PendingTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_CONTRACT, $adapter, null);
                    return new \Admin\Model\PendingTable($tableGateway);
                },
                'Admin\Model\BillTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_BILL, $adapter, null);
                    return new \Admin\Model\BillTable($tableGateway);
                },
                'Admin\Model\SalesTargetTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_SALES_TARGET, $adapter, null);
                    return new \Admin\Model\SalesTargetTable($tableGateway);
                },
                'Admin\Model\MarketingReportTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_MARKETING_REPORT, $adapter, null);
                    return new \Admin\Model\MarketingReportTable($tableGateway);
                },
                'Admin\Model\MarketingAdsTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_MARKETING_ADS, $adapter, null);
                    return new \Admin\Model\MarketingAdsTable($tableGateway);
                },
                'Admin\Model\MaterialTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_MATERIAL, $adapter, null);
                    return new \Admin\Model\MaterialTable($tableGateway);
                },
                'Admin\Model\EventTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_EVENT, $adapter, null);
                    return new \Admin\Model\EventTable($tableGateway);
                },
                'Admin\Model\EventContactTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_EVENT_CONTACT, $adapter, null);
                    return new \Admin\Model\EventContactTable($tableGateway);
                },
                'Admin\Model\ProductTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_PRODUCT, $adapter, null);
                    return new \Admin\Model\ProductTable($tableGateway);
                },
                'Admin\Model\EduClassTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_EDU_CLASS, $adapter, null);
                    return new \Admin\Model\EduClassTable($tableGateway);
                },
                'Admin\Model\HistoryTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_HISTORY, $adapter, null);
                    return new \Admin\Model\HistoryTable($tableGateway); 
                },
                'Admin\Model\LogsTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig'); 
                    $tableGateway = new TableGateway(TABLE_LOGS, $adapter, null);
                    return new \Admin\Model\LogsTable($tableGateway); 
                },
                'Admin\Model\TaskCategoryTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_TASK_CATEGORY, $adapter, null);
                    return new \Admin\Model\TaskCategoryTable($tableGateway);
                },
                'Admin\Model\TaskProjectTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_TASK_PROJECT, $adapter, null);
                    return new \Admin\Model\TaskProjectTable($tableGateway);
                },
                'Admin\Model\TaskProjectContentTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_TASK_PROJECT_CONTENT, $adapter, null);
                    return new \Admin\Model\TaskProjectContentTable($tableGateway);
                },
                'Admin\Model\TaskTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_TASK, $adapter, null);
                    return new \Admin\Model\TaskTable($tableGateway);
                },
                'Admin\Model\CampaignTable'	=> function ($sm) {
                	$adapter = $sm->get('dbConfig');
                	$tableGateway = new TableGateway(TABLE_CAMPAIGN, $adapter, null);
                	return new \Admin\Model\CampaignTable($tableGateway);
                },
                'Admin\Model\CampaignDataTable'	=> function ($sm) {
                	$adapter = $sm->get('dbConfig');
                	$tableGateway = new TableGateway(TABLE_CAMPAIGN_DATA, $adapter, null);
                	return new \Admin\Model\CampaignDataTable($tableGateway);
                },
                'Admin\Model\MatterTable'	=> function ($sm) {
                	$adapter = $sm->get('dbConfig');
                	$tableGateway = new TableGateway(TABLE_MATTER, $adapter, null);
                	return new \Admin\Model\MatterTable($tableGateway);
                },
                'Admin\Model\BcTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_BC, $adapter, null);
                    return new \Admin\Model\BcTable($tableGateway);
                },
                'Admin\Model\BcBillTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_BC_BILL, $adapter, null);
                    return new \Admin\Model\BcBillTable($tableGateway);
                },
                'Admin\Model\FormDataTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_FORM_DATA, $adapter, null);
                    return new \Admin\Model\FormDataTable($tableGateway);
                },
                'Admin\Model\LocationsTable' => function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_LOCATIONS, $adapter, null);
                    return new \Admin\Model\LocationsTable($tableGateway);
                },
                'Notifycation\Model\NotifyTable'    => function ($sm) {
                    $adapter = $sm->get('dbNotify');
                    $tableGateway = new TableGateway(TABLE_NOTIFY, $adapter, null);
                    return new \Notifycation\Model\NotifyTable($tableGateway);
                },
                'Admin\Model\HistoryImportTable' => function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_HISTORY_IMPORT, $adapter, null);
                    return new \Admin\Model\HistoryImportTable($tableGateway);
                },
                'Admin\Model\ContractOwedTable' => function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_CONTRACT, $adapter, null);
                    return new \Admin\Model\ContractOwedTable($tableGateway);
                },
                'Admin\Model\CheckTable' => function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_CONTRACT, $adapter, null);
                    return new \Admin\Model\CheckTable($tableGateway);
                },
                'Admin\Model\LinkCheckingTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_LINK_CHECKING, $adapter, null);
                    return new \Admin\Model\LinkCheckingTable($tableGateway);
                },
                'Admin\Model\ProductListedTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_PRODUCT_LISTED, $adapter, null);
                    return new \Admin\Model\ProductListedTable($tableGateway);
                },
                'Admin\Model\ColorGroupTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_COLOR, $adapter, null);
                    return new \Admin\Model\ColorGroupTable($tableGateway);
                },
                'Admin\Model\CarpetColorTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_COLOR, $adapter, null);
                    return new \Admin\Model\CarpetColorTable($tableGateway);
                },
                'Admin\Model\TangledColorTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_COLOR, $adapter, null);
                    return new \Admin\Model\TangledColorTable($tableGateway);
                },
                'Admin\Model\DataConfigTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_DATA_CONFIG, $adapter, null);
                    return new \Admin\Model\DataConfigTable($tableGateway);
                },
                'Admin\Model\CheckInTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_CHECK_IN, $adapter, null);
                    return new \Admin\Model\CheckInTable($tableGateway);
                },
                'Admin\Model\TargetTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_TARGET, $adapter, null);
                    return new \Admin\Model\TargetTable($tableGateway);
                },
                'Admin\Model\EvaluateTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_EVALUATE, $adapter, null);
                    return new \Admin\Model\EvaluateTable($tableGateway);
                },
                'Admin\Model\NotifiTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_NOTIFI, $adapter, null);
                    return new \Admin\Model\NotifiTable($tableGateway);
                },
                'Admin\Model\NotifiUserTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_NOTIFI_USER, $adapter, null);
                    return new \Admin\Model\NotifiUserTable($tableGateway);
                },
                'Admin\Model\ComboProductTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_COMBO_PRODUCT, $adapter, null);
                    return new \Admin\Model\ComboProductTable($tableGateway);
                },
                'Admin\Model\KovBranchesTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_KOV_BRANCHES, $adapter, null);
                    return new \Admin\Model\KovBranchesTable($tableGateway);
                },
                'Admin\Model\KovProductsTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_KOV_PRODUCTS, $adapter, null);
                    return new \Admin\Model\KovProductsTable($tableGateway);
                },
                'Admin\Model\KovProductBranchTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_KOV_PRODUCT_BRANCH, $adapter, null);
                    return new \Admin\Model\KovProductBranchTable($tableGateway);
                },
                'Admin\Model\KovDiscountsTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_KOV_DISCOUNTS, $adapter, null);
                    return new \Admin\Model\KovDiscountsTable($tableGateway);
                },
                'Admin\Model\ProductReturnTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_PRODUCT_RETURN, $adapter, null);
                    return new \Admin\Model\ProductReturnTable($tableGateway);
                },
                'Admin\Model\ProductReturnKovTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_PRODUCT_RETURN_KOV, $adapter, null);
                    return new \Admin\Model\ProductReturnKovTable($tableGateway);
                },
                'Admin\Model\ZaloNotifyConfigTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_ZALO_NOTIFY_CONFIG, $adapter, null);
                    return new \Admin\Model\ZaloNotifyConfigTable($tableGateway);
                },
                'Admin\Model\ZaloNotifyResultTable'	=> function ($sm) {
                    $adapter = $sm->get('dbConfig');
                    $tableGateway = new TableGateway(TABLE_ZALO_NOTIFY_RESULT, $adapter, null);
                    return new \Admin\Model\ZaloNotifyResultTable($tableGateway);
                },
                
                // Khai báo xác thực đăng nhập
                'AuthenticateService'	=> function ($sm) {
                    $dbTableAdapter = new \Zend\Authentication\Adapter\DbTable($sm->get('dbConfig'), TABLE_USER, 'username', 'password', 'MD5(?)');
                    $dbTableAdapter->getDbSelect()->where->equalTo('status', 1);
                    
                    $authenticateServiceObj = new \Zend\Authentication\AuthenticationService(null, $dbTableAdapter);
                    return $authenticateServiceObj;
                },
                'MyAuth'	=> function ($sm) {
                    return new \ZendX\System\Authenticate($sm->get('AuthenticateService'));
                },
            ),
            'invokables' => array(
                'Zend\Authentication\AuthenticationService' => 'Zend\Authentication\AuthenticationService',
            ),
        );
    }

    public function getFormElementConfig() {
        return array(
            'factories' => array(
            	'formAdminUser' => function($sm) {
            		$myForm	= new \Admin\Form\User($sm);
            		$myForm->setInputFilter(new \Admin\Filter\User());
            		return $myForm;
            	},
            	'formAdminTeacher' => function($sm) {
            		$myForm	= new \Admin\Form\Teacher($sm);
            		$myForm->setInputFilter(new \Admin\Filter\Teacher());
            		return $myForm;
            	},
            	'formAdminCoach' => function($sm) {
            		$myForm	= new \Admin\Form\Coach($sm);
            		$myForm->setInputFilter(new \Admin\Filter\Coach());
            		return $myForm;
            	},
            	'formAdminPermission' => function($sm) {
            		$myForm	= new \Admin\Form\Permission($sm);
            		$myForm->setInputFilter(new \Admin\Filter\Permission());
            		return $myForm;
            	},
            	'formAdminPermissionList' => function($sm) {
            		$myForm	= new \Admin\Form\PermissionList($sm);
            		$myForm->setInputFilter(new \Admin\Filter\PermissionList());
            		return $myForm;
            	},
                'formAdminDynamic' => function($sm) {
                    $myForm	= new \Admin\Form\Dynamic($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Dynamic());
                    return $myForm;
                },
                'formAdminDocument' => function($sm) {
                    $myForm	= new \Admin\Form\Document($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Document());
                    return $myForm;
                },
                'formAdminSetting' => function($sm) {
                    $myForm	= new \Admin\Form\Setting($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Setting());
                    return $myForm;
                },
                'formAdminContact' => function($sm) {
                    $myForm	= new \Admin\Form\Contact($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Contact());
                    return $myForm;
                },
                'formAdminContract' => function($sm) {
                    $myForm	= new \Admin\Form\Contract($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Contract());
                    return $myForm;
                },
                'formAdminContractDetail' => function($sm) {
                    $myForm	= new \Admin\Form\ContractDetail($sm);
                    $myForm->setInputFilter(new \Admin\Filter\ContractDetail());
                    return $myForm;
                },
                'formAdminPending' => function($sm) {
                    $myForm	= new \Admin\Form\Pending($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Pending());
                    return $myForm;
                },
                'formAdminSalesTarget' => function($sm) {
                    $myForm	= new \Admin\Form\SalesTarget($sm);
                    $myForm->setInputFilter(new \Admin\Filter\SalesTarget());
                    return $myForm;
                },
                'formAdminMarketingReport' => function($sm) {
                    $myForm	= new \Admin\Form\MarketingReport($sm);
                    $myForm->setInputFilter(new \Admin\Filter\MarketingReport());
                    return $myForm;
                },
                'formAdminMarketingAds' => function($sm) {
                    $myForm	= new \Admin\Form\MarketingAds($sm);
                    $myForm->setInputFilter(new \Admin\Filter\MarketingAds());
                    return $myForm;
                },
                'formAdminMarketingTarget' => function($sm) {
                    $myForm	= new \Admin\Form\MarketingTarget($sm);
                    $myForm->setInputFilter(new \Admin\Filter\MarketingTarget());
                    return $myForm;
                },
                'formAdminMaterial' => function($sm) {
                    $myForm	= new \Admin\Form\Material($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Material());
                    return $myForm;
                },
                'formAdminEventDemo' => function($sm) {
                    $myForm	= new \Admin\Form\EventDemo($sm);
                    $myForm->setInputFilter(new \Admin\Filter\EventDemo());
                    return $myForm;
                },
                'formAdminEventTest' => function($sm) {
                    $myForm	= new \Admin\Form\EventTest($sm);
                    $myForm->setInputFilter(new \Admin\Filter\EventTest());
                    return $myForm;
                },
                'formAdminEventWorkshop' => function($sm) {
                    $myForm	= new \Admin\Form\EventWorkshop($sm);
                    $myForm->setInputFilter(new \Admin\Filter\EventWorkshop());
                    return $myForm;
                },
                'formAdminEventContact' => function($sm) {
                    $myForm	= new \Admin\Form\EventContact($sm);
                    $myForm->setInputFilter(new \Admin\Filter\EventContact());
                    return $myForm;
                },
                'formAdminProduct' => function($sm) {
                    $myForm	= new \Admin\Form\Product($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Product());
                    return $myForm;
                },
                'formAdminEduClass' => function($sm) {
                    $myForm	= new \Admin\Form\EduClass($sm);
                    $myForm->setInputFilter(new \Admin\Filter\EduClass());
                    return $myForm;
                },
                'formAdminLogs' => function($sm) {
                    $myForm	= new \Admin\Form\Logs($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Logs());
                    return $myForm;
                },
                'formAdminLogin' => function($sm) {
                    $myForm	= new \Admin\Form\Login($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Login());
                    return $myForm;
                },
                'formAdminTaskCategory' => function($sm) {
                    $myForm	= new \Admin\Form\TaskCategory($sm);
                    $myForm->setInputFilter(new \Admin\Filter\TaskCategory());
                    return $myForm;
                },
                'formAdminTaskProject' => function($sm) {
                    $myForm	= new \Admin\Form\TaskProject($sm);
                    $myForm->setInputFilter(new \Admin\Filter\TaskProject());
                    return $myForm;
                },
                'formAdminTaskProjectContent' => function($sm) {
                    $myForm	= new \Admin\Form\TaskProjectContent($sm);
                    $myForm->setInputFilter(new \Admin\Filter\TaskProjectContent());
                    return $myForm;
                },
                'formAdminTask' => function($sm) {
                    $myForm	= new \Admin\Form\Task($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Task());
                    return $myForm;
                },
                'formAdminCampaign' => function($sm) {
                	$myForm	= new \Admin\Form\Campaign($sm);
                	$myForm->setInputFilter(new \Admin\Filter\Campaign());
                	return $myForm;
                },
                'formAdminCampaignData' => function($sm) {
                	$myForm	= new \Admin\Form\CampaignData($sm);
                	$myForm->setInputFilter(new \Admin\Filter\CampaignData());
                	return $myForm;
                },
                'formAdminBc' => function($sm) {
                    $myForm	= new \Admin\Form\Bc($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Bc());
                    return $myForm;
                },
                'formAdminFormData' => function($sm) {
                    $myForm	= new \Admin\Form\FormData($sm);
                    $myForm->setInputFilter(new \Admin\Filter\FormData());
                    return $myForm;
                },
                'formAdminLinkChecking' => function($sm) {
                    $myForm	= new \Admin\Form\LinkChecking($sm);
                    $myForm->setInputFilter(new \Admin\Filter\LinkChecking());
                    return $myForm;
                },
                'formAdminProductListed' => function($sm) {
                    $myForm	= new \Admin\Form\ProductListed($sm);
                    $myForm->setInputFilter(new \Admin\Filter\ProductListed());
                    return $myForm;
                },
                'formAdminColorGroup' => function($sm) {
                    $myForm	= new \Admin\Form\ColorGroup($sm);
                    $myForm->setInputFilter(new \Admin\Filter\ColorGroup());
                    return $myForm;
                },
                'formAdminCarpetColor' => function($sm) {
                    $myForm	= new \Admin\Form\CarpetColor($sm);
                    $myForm->setInputFilter(new \Admin\Filter\CarpetColor());
                    return $myForm;
                },
                'formAdminTangledColor' => function($sm) {
                    $myForm	= new \Admin\Form\TangledColor($sm);
                    $myForm->setInputFilter(new \Admin\Filter\TangledColor());
                    return $myForm;
                },
                'formAdminDataConfig' => function($sm) {
                    $myForm	= new \Admin\Form\DataConfig($sm);
                    $myForm->setInputFilter(new \Admin\Filter\DataConfig());
                    return $myForm;
                },
                'formAdminCheckIn' => function($sm) {
                    $myForm	= new \Admin\Form\CheckIn($sm);
                    $myForm->setInputFilter(new \Admin\Filter\CheckIn());
                    return $myForm;
                },
                'formAdminTarget' => function($sm) {
                    $myForm	= new \Admin\Form\Target($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Target());
                    return $myForm;
                },
                'formAdminEvaluate' => function($sm) {
                    $myForm	= new \Admin\Form\Evaluate($sm);
                    $myForm->setInputFilter(new \Admin\Filter\Evaluate());
                    return $myForm;
                },
                'formAdminPermissionList' => function($sm) {
                    $myForm	= new \Admin\Form\PermissionList($sm);
                    $myForm->setInputFilter(new \Admin\Filter\PermissionList());
                    return $myForm;
                },
                'formAdminComboProduct' => function($sm) {
                    $myForm	= new \Admin\Form\ComboProduct($sm);
                    $myForm->setInputFilter(new \Admin\Filter\ComboProduct());
                    return $myForm;
                },
                'formAdminKovProducts' => function($sm) {
                    $myForm	= new \Admin\Form\KovProducts($sm);
                    $myForm->setInputFilter(new \Admin\Filter\KovProducts());
                    return $myForm;
                },
                'formAdminKovProductBranch' => function($sm) {
                    $myForm	= new \Admin\Form\KovProductBranch($sm);
                    $myForm->setInputFilter(new \Admin\Filter\KovProductBranch());
                    return $myForm;
                },
                'formAdminKovDiscounts' => function($sm) {
                    $myForm	= new \Admin\Form\KovDiscounts($sm);
                    $myForm->setInputFilter(new \Admin\Filter\KovDiscounts());
                    return $myForm;
                },
                'formAdminProductReturn' => function($sm) {
                    $myForm	= new \Admin\Form\ProductReturn($sm);
                    $myForm->setInputFilter(new \Admin\Filter\ProductReturn());
                    return $myForm;
                },
                'formAdminZaloNotifyConfig' => function($sm) {
                    $myForm	= new \Admin\Form\ZaloNotifyConfig($sm);
                    $myForm->setInputFilter(new \Admin\Filter\ZaloNotifyConfig());
                    return $myForm;
                },
                'formAdminZaloNotifyResult' => function($sm) {
                    $myForm	= new \Admin\Form\ZaloNotifyResult($sm);
                    $myForm->setInputFilter(new \Admin\Filter\ZaloNotifyResult());
                    return $myForm;
                },
            )
        );
    }

    public function getViewHelperConfig() {
        return array(
            'invokables' => array(
                'xViewElementError'	    => '\ZendX\View\Helper\ElementError',
                'xViewElementErrors'	=> '\ZendX\View\Helper\ElementErrors',
                'xViewInfoPrice'	    => '\ZendX\View\Helper\InfoPrice',
                'xFormSelect'           => '\ZendX\Form\View\Helper\FormSelect',
                'xFormHidden'           => '\ZendX\Form\View\Helper\FormHidden',
                'xFormInput'            => '\ZendX\Form\View\Helper\FormInput',
                'xFormButton'           => '\ZendX\Form\View\Helper\FormButton',
                'linkAdmin'             => '\ZendX\View\Helper\Url\LinkAdmin',
                'linkAdminSort'         => '\ZendX\View\Helper\Url\LinkAdminSort',
                'linkAdminStatus'       => '\ZendX\View\Helper\Url\LinkAdminStatus',
                'linkAdminHtml'         => '\ZendX\View\Helper\Url\LinkAdminHtml',
            )
        );
    }
}
