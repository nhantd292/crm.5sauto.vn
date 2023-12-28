<?php
$routeReport = array(
    'type' => 'Segment',
    'options' => array (
        'route' => '/xreport',
        'defaults' => array (
            '__NAMESPACE__' => 'Report\Controller',
            'controller' 	=> 'Index',
            'action' 		=> 'index'
        )
    ),
    'may_terminate' => true,
    'child_routes' => array (
        'default' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/[:controller[/:action[/id/:id[/code/:code]]]][/]',
                'constraints' => array (
                    'controller' 	=> '[a-zA-Z0-9_-]*',
                    'action' 		=> '[a-zA-Z0-9_-]*',
                    'id' 		    => '[a-zA-Z0-9_-]*',
                    'code' 		    => '[a-zA-Z0-9_-]*',
                ),
                'defaults' => array ()
            )
        ),
        'type' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/[:controller[/:action[/type/:type[/id/:id[/code/:code]]]]][/]',
                'constraints' => array (
                    'controller' 	=> '[a-zA-Z0-9_-]*',
                    'action' 		=> '[a-zA-Z0-9_-]*',
                    'type' 		    => '[a-zA-Z0-9_-]*',
                    'id' 		    => '[a-zA-Z0-9_-]*',
                    'code' 		    => '[a-zA-Z0-9_-]*',
                ),
                'defaults' => array ()
            )
        ),
        'paginator' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/:controller/index/page/:page[/]',
                'constraints' => array (
                    'controller'    => '[a-zA-Z0-9_-]*',
                    'page'          => '[0-9]*'
                ),
                'defaults' => array ()
            )
        )
    )
);

return array (
    'router' => array(
        'routes' => array(
            'routeReport' => $routeReport,
        ),
    )
);