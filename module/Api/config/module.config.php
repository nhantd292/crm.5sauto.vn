<?php

namespace Api;

return array (
	'controllers' => array(
		'invokables' => array(
			'Api\Controller\SaleFormData' 			=> Controller\SaleFormDataController::class,
			'Api\Controller\SaleCompanyBranch' 		=> Controller\SaleCompanyBranchController::class,
			'Api\Controller\SaleDocument' 			=> Controller\SaleDocumentController::class,
			'Api\Controller\SaleContact' 			=> Controller\SaleContactController::class,
			'Api\Controller\OfflineTrainingClass' 	=> Controller\OfflineTrainingClassController::class,
			'Api\Controller\OfflineProduct' 		=> Controller\OfflineProductController::class,
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


