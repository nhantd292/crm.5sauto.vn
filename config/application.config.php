<?php

return array(
    'modules' => array('Admin', 'Report', 'Api', 'Notifycation'),
    'module_listener_options' => array(
        'module_paths' => array(
            './module',
            './vendor',
        ),

        'config_glob_paths' => array(
            'config/autoload/{{,*.}global,{,*.}local}.php',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'cache' => function() {
                return Zend\Cache\StorageFactory::factory(
                    array(
                        'adapter' => array(
                            'name' => 'filesystem',
                            'options' => array(
                                'dirLevel' => 0,
                                'cacheDir' => PATH_PUBLIC . '/cache',
                                'dirPermission' => 0755,
                                'filePermission' => 0664,
                                'namespaceSeparator' => '-xx-'
                            ),
                        ),
                        'plugins' => array('serializer'),
                    )
                );
            }
        ),
    ),
);
