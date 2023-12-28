<?php

namespace Report;

return array (
	'controllers' => array(
		'invokables' => array(
			'Report\Controller\Index'        => Controller\IndexController::class,
			'Report\Controller\Revenue'      => Controller\RevenueController::class,
			'Report\Controller\Contract'     => Controller\ContractController::class,
			'Report\Controller\Contact'      => Controller\ContactController::class,
			'Report\Controller\Edu'          => Controller\EduController::class,
            'Report\Controller\Production'   => Controller\ProductionController::class,
            'Report\Controller\Marketing'    => Controller\MarketingController::class,
            'Report\Controller\Sale'         => Controller\SaleController::class,
            'Report\Controller\Check'        => Controller\CheckController::class,
            'Report\Controller\Care'         => Controller\CareController::class,
            'Report\Controller\Acounting'    => Controller\AcountingController::class,
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
        )
    ),
);


