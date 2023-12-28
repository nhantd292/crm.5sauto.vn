<?php

namespace Admin;

return array (
	'controllers' => array(
		'invokables' => array(
			'Admin\Controller\Api'                    => Controller\ApiController::class,
			'Admin\Controller\Index'                  => Controller\IndexController::class,
		    'Admin\Controller\Dynamic'                => Controller\DynamicController::class,
		    'Admin\Controller\Document'               => Controller\DocumentController::class,
		    'Admin\Controller\Notice'                 => Controller\NoticeController::class,
		    'Admin\Controller\Setting'                => Controller\SettingController::class,
			'Admin\Controller\User'                   => Controller\UserController::class,
			'Admin\Controller\Permission'             => Controller\PermissionController::class,
			'Admin\Controller\PermissionList'         => Controller\PermissionListController::class,
			'Admin\Controller\Contact'                => Controller\ContactController::class,
			'Admin\Controller\Contract'               => Controller\ContractController::class,
			'Admin\Controller\ContractDetail'         => Controller\ContractDetailController::class,
			'Admin\Controller\ContractMe'             => Controller\ContractMeController::class,
			'Admin\Controller\ContractOwed'           => Controller\ContractOwedController::class,
			'Admin\Controller\ContractDebtOld'        => Controller\ContractDebtOldController::class,
			'Admin\Controller\ContractDebtNew'        => Controller\ContractDebtNewController::class,
			'Admin\Controller\Bill'                   => Controller\BillController::class,
			'Admin\Controller\Product'                => Controller\ProductController::class,
			'Admin\Controller\ProductSetting'         => Controller\ProductSettingController::class,
			'Admin\Controller\EduClass'               => Controller\EduClassController::class,
			'Admin\Controller\SalesTarget'            => Controller\SalesTargetController::class,
			'Admin\Controller\MarketingReport'        => Controller\MarketingReportController::class,
			'Admin\Controller\MarketingTarget'        => Controller\MarketingTargetController::class,
			'Admin\Controller\Material'               => Controller\MaterialController::class,
		    'Admin\Controller\EventDemo'              => Controller\EventDemoController::class,
		    'Admin\Controller\EventTest'              => Controller\EventTestController::class,
		    'Admin\Controller\EventWorkshop'          => Controller\EventWorkshopController::class,
		    'Admin\Controller\EventContact'		      => Controller\EventContactController::class,
			'Admin\Controller\History'                => Controller\HistoryController::class,
			'Admin\Controller\Logs'                   => Controller\LogsController::class,
            'Admin\Controller\Locations'              => Controller\LocationsController::class,
		    'Admin\Controller\TaskCategory'           => Controller\TaskCategoryController::class,
		    'Admin\Controller\TaskProject'            => Controller\TaskProjectController::class,
		    'Admin\Controller\TaskProjectContent'     => Controller\TaskProjectContentController::class,
		    'Admin\Controller\Task'                   => Controller\TaskController::class,
		    'Admin\Controller\Campaign'               => Controller\CampaignController::class,
		    'Admin\Controller\CampaignData'           => Controller\CampaignDataController::class,
		    'Admin\Controller\Teacher'                => Controller\TeacherController::class,
		    'Admin\Controller\Coach'                  => Controller\CoachController::class,
		    'Admin\Controller\Pending'                => Controller\PendingController::class,
		    'Admin\Controller\Bc'                     => Controller\BcController::class,
            'Admin\Controller\FormData'               => Controller\FormDataController::class,
            'Admin\Controller\ContactData'            => Controller\ContactDataController::class,
            'Admin\Controller\Production'             => Controller\ProductionController::class,
            'Admin\Controller\Check'                  => Controller\CheckController::class,
		    'Admin\Controller\LinkChecking'           => Controller\LinkCheckingController::class,
		    'Admin\Controller\ProductListed'          => Controller\ProductListedController::class,
		    'Admin\Controller\CapitalDefault'         => Controller\CapitalDefaultController::class,
		    'Admin\Controller\ColorGroup'             => Controller\ColorGroupController::class,
		    'Admin\Controller\CarpetColor'            => Controller\CarpetColorController::class,
		    'Admin\Controller\TangledColor'           => Controller\TangledColorController::class,
		    'Admin\Controller\DataConfig'             => Controller\DataConfigController::class,
		    'Admin\Controller\CheckIn'                => Controller\CheckInController::class,
		    'Admin\Controller\Target'                 => Controller\TargetController::class,
		    'Admin\Controller\Evaluate'               => Controller\EvaluateController::class,
		    'Admin\Controller\NotifiUser'             => Controller\NotifiUserController::class,
		    'Admin\Controller\ComboProduct'           => Controller\ComboProductController::class,
		    'Admin\Controller\KovBranches'            => Controller\KovBranchesController::class,
		    'Admin\Controller\KovProducts'            => Controller\KovProductsController::class,
		    'Admin\Controller\KovDiscounts'           => Controller\KovDiscountsController::class,
		    'Admin\Controller\ProductReturn'          => Controller\ProductReturnController::class,
		)
	),
	'view_manager' => array(
		'doctype'					=> 'HTML5',
		'display_not_found_reason' 	=> (APPLICATION_ENV == 'development') ? true : false,
		'not_found_template'       	=> 'error/404',
			
		'display_exceptions'       	=> (APPLICATION_ENV == 'development') ? true : false,
		'exception_template'       	=> 'error/index',
				
		'template_path_stack'		=> array(__DIR__ . '/../view'),
		'template_map' 				=> array(
			'layout/layout'         => PATH_TEMPLATE . '/frontend/main.phtml',
			'layout/frontend'       => PATH_TEMPLATE . '/frontend/main.phtml',
			'layout/backend'        => PATH_TEMPLATE . '/backend/main.phtml',
		    'error/layout'          => PATH_TEMPLATE . '/error/layout.phtml',
			'error/404'             => PATH_TEMPLATE . '/error/404.phtml',
			'error/index'           => PATH_TEMPLATE . '/error/index.phtml',
		),
		'default_template_suffix'  	=> 'phtml',
		'layout'					=> 'layout/layout'
	),
    'view_helper_config' => array(
        'flashmessenger' => array(
            'message_open_format' => '<div class="alert alert-block alert-success"><button type="button" class="close" data-dismiss="alert" aria-hidden="true"></button><p>',
            'message_close_string' => '</p></div>',
            'message_separator_string' => '',
        ),
    ),
);