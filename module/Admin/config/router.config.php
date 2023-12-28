<?php
$routeHome = array(
    'type' => 'Literal',
    'options' => array (
        'route' => '/',
        'defaults' => array (
            '__NAMESPACE__' => 'Admin\Controller',
            'controller' 	=> 'Index',
            'action' 		=> 'index'
        )
    )
);

// Route Admin
$routeAdmin = array(
    'type' => 'Segment',
    'options' => array (
        'route' => '/xadmin',
        'defaults' => array (
            '__NAMESPACE__' => 'Admin\Controller',
            'controller' 	=> 'Index',
            'action' 		=> 'index'
        )
    ),
    'may_terminate' => true,
    'child_routes' => array (
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
        'notify' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/[:controller[/:action[/nid/:nid[/type/:type]]]][/]',
                'constraints' => array (
                    'controller'    => '[a-zA-Z0-9_-]*',
                    'action'        => '[a-zA-Z0-9_-]*',
                    'id'            => '[a-zA-Z0-9_-]*',
                    'code'          => '[a-zA-Z0-9_-]*',
                ),
                'defaults' => array ()
            )
        ),
        'paginator' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/:controller/:action/page/:page[/]',
                'constraints' => array (
                    'controller'    => '[a-zA-Z0-9_-]*',
                    'page'          => '[0-9]*'
                ),
                'defaults' => array ()
            )
        )
    )
);

// Route Admin Nested
$routeAdminNested = array(
    'type' => 'Segment',
    'options' => array (
        'route' => '/xadmin-nested',
        'defaults' => array (
            '__NAMESPACE__' => 'Admin\Controller',
            'controller' 	=> 'Nested',
            'action' 		=> 'index'
        )
    ),
    'may_terminate' => true,
    'child_routes' => array (
        'default' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/[:controller[/:action[/:code[/:id]]]][/]',
                'constraints' => array (
                    'controller' 	=> '[a-zA-Z0-9-]*',
                    'action' 		=> '[a-zA-Z0-9-]*',
                    'code' 		    => '[a-zA-Z0-9.]*',
                    'id' 		    => '[a-zA-Z0-9-]*'
                ),
                'defaults' => array (
                )
            )
        ),
        'index' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '[/:controller[/:action[/:code]]][/]',
                'constraints' => array (
                    'nested' 	    => '[a-zA-Z0-9-]*',
                    'controller' 	=> '[a-zA-Z0-9-]*',
                    'action' 		=> '[a-zA-Z0-9-]*',
                    'code' 		    => '[a-zA-Z0-9.]*'
                ),
                'defaults' => array (
                    'nested'        => 'nested',
                    'action'        => 'index'
                )
            )
        ),
        'add' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/[:controller[/:action[/:code[/:type[/:reference]]]]][/]',
                'constraints' => array (
                    'controller' 	=> '[a-zA-Z0-9-]*',
                    'action' 		=> '[a-zA-Z0-9-]*',
                    'code' 		    => '[a-zA-Z0-9.]*',
                    'type' 	        => '[a-zA-Z0-9-]*',
                    'reference' 	=> '[a-zA-Z0-9-]*'
                ),
                'defaults' => array (
                    'action'        => 'add'
                )
            )
        ),
        'paginator' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/:controller/index/:code/page/:page[/]',
                'constraints' => array (
                    'controller'    => '[a-zA-Z0-9-]*',
                    'code' 		    => '[a-zA-Z0-9.]*',
                    'page'          => '[0-9]*'
                ),
                'defaults' => array ()
            )
        )
    )
);

// Route Admin Document
$routeAdminDocument = array(
    'type' => 'Segment',
    'options' => array (
        'route' => '/xadmin-document',
        'defaults' => array (
            '__NAMESPACE__' => 'Admin\Controller',
            'controller' 	=> 'Document',
            'action' 		=> 'index'
        )
    ),
    'may_terminate' => true,
    'child_routes' => array (
        'default' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/[:slug[/:action[/id/:id[/code/:code]]]][/]',
                'constraints' => array (
                    'slug' 	        => '[a-zA-Z0-9-]*',
                    'action' 		=> '[a-zA-Z0-9-]*',
                    'id' 		    => '[a-zA-Z0-9-]*',
                    'code' 		    => '[a-zA-Z0-9-]*',
                ),
                'defaults' => array (
                    'controller' => 'document'
                )
            )
        ),
        'paginator' => array(
            'type' => 'Segment',
            'options' => array (
                'route' => '/:slug/index/page/:page[/]',
                'constraints' => array (
                    'slug' 	        => '[a-zA-Z0-9-]*',
                    'page'          => '[0-9]*'
                ),
                'defaults' => array (
                    'controller' => 'document'
                )
            )
        )
    )
);

return array (
    'router' => array(
        'routes' => array(
            'routeHome'             => $routeHome,
            'routeAdmin'            => $routeAdmin,
            'routeAdminNested'      => $routeAdminNested,
            'routeAdminDocument'    => $routeAdminDocument,
        ),
    )
);