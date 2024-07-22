<?php
return [
    'backend' => [
        'frontName' => 'admin_smrtdb'
    ],
    'domains' => [
        'smartblinds.nl' => [
            'MAGE_RUN_CODE' => 'default',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'smartblinds.be' => [
            'MAGE_RUN_CODE' => 'be',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'smartblinds.com' => [
            'MAGE_RUN_CODE' => 'com',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'smartblinds.co.uk' => [
            'MAGE_RUN_CODE' => 'uk',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'smartblinds.de' => [
            'MAGE_RUN_CODE' => 'de',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'smartblinds.at' => [
            'MAGE_RUN_CODE' => 'at',
            'MAGE_RUN_TYPE' => 'store'
        ]
    ],
    'remote_storage' => [
        'driver' => 'file'
    ],
    'queue' => [
        'consumers_wait_for_messages' => 1
    ],
    'crypt' => [
        'key' => '267311dd48a3395f0e1b44f6aaf9fd57'
    ],
    'db' => [
        'table_prefix' => '',
        'connection' => [
            'default' => [
                'host' => 'localhost',
                'dbname' => 'smartblinds_live',
                'username' => 'smartblinds_live',
                'password' => 'bEu80gOZlDiM',
                'model' => 'mysql4',
                'engine' => 'innodb',
                'initStatements' => 'SET NAMES utf8;',
                'active' => '1',
                'driver_options' => [
                    1014 => false
                ]
            ]
        ]
    ],
    'resource' => [
        'default_setup' => [
            'connection' => 'default'
        ]
    ],
    'x-frame-options' => 'SAMEORIGIN',
    'MAGE_MODE' => 'production',
    'session' => [
        'save' => 'redis',
        'redis' => [
            'host' => '/home/livesmartblinds/domains/smartblinds.nl/var/run/redis-session.sock',
            'port' => '0',
            'password' => '',
            'timeout' => '2.5',
            'persistent_identifier' => '',
            'database' => '0',
            'compression_threshold' => '2048',
            'compression_library' => 'gzip',
            'log_level' => '1',
            'max_concurrency' => '6',
            'break_after_frontend' => '5',
            'break_after_adminhtml' => '30',
            'first_lifetime' => '600',
            'bot_first_lifetime' => '60',
            'bot_lifetime' => '7200',
            'disable_locking' => '0',
            'min_lifetime' => '60',
            'max_lifetime' => '2592000'
        ]
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => 'f06_',
                'backend' => 'Magento\\Framework\\Cache\\Backend\\Redis',
                'backend_options' => [
                    'server' => '/home/livesmartblinds/domains/smartblinds.nl/var/run/redis-backend.sock',
                    'database' => '0',
                    'port' => '0',
                    'password' => '',
                    'compress_data' => '1',
                    'compression_lib' => ''
                ]
            ],
            'page_cache' => [
                'id_prefix' => 'f06_'
            ]
        ],
        'allow_parallel_generation' => false,
        'graphql' => [
            'id_salt' => 'P2m9jYGXS5tn5lkJ970k4tluyue8qlOQ'
        ]
    ],
    'lock' => [
        'provider' => 'db',
        'config' => [
            'prefix' => ''
        ]
    ],
    'directories' => [
        'document_root_is_pub' => true
    ],
    'system' => [
        'default' => [
            'catalog' => [
                'search' => [
                    'engine' => 'elasticsearch7',
                    'elasticsearch7_server_port' => '19200'
                ]
            ]
        ]
    ],
    'cache_types' => [
        'config' => 1,
        'layout' => 1,
        'block_html' => 1,
        'collections' => 1,
        'reflection' => 1,
        'db_ddl' => 1,
        'compiled_config' => 1,
        'eav' => 1,
        'customer_notification' => 1,
        'config_integration' => 1,
        'config_integration_api' => 1,
        'full_page' => 1,
        'config_webservice' => 1,
        'translate' => 1,
        'vertex' => 1,
        'amasty_shopby' => 1,
        'ec_cache' => 1
    ],
    'downloadable_domains' => [
        'coulisse.loc'
    ],
    'install' => [
        'date' => 'Mon, 22 Mar 2021 16:59:42 +0000'
    ],
    'http_cache_hosts' => [
        [
            'host' => '127.0.0.1',
            'port' => '6181'
        ]
    ],
    'is_secure' => true
];
