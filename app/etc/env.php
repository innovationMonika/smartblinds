<?php
return [
    'backend' => [
        'frontName' => 'admin'
    ],
    'domains' => [
        'staging.commerce.smartblinds.nl' => [
            'MAGE_RUN_CODE' => 'default',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'staging.commerce.smartblinds.be' => [
            'MAGE_RUN_CODE' => 'be',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'staging.commerce.smartblinds.com' => [
            'MAGE_RUN_CODE' => 'com',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'staging.commerce.smartblinds.co.uk' => [
            'MAGE_RUN_CODE' => 'uk',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'staging.commerce.smartblinds.de' => [
            'MAGE_RUN_CODE' => 'de',
            'MAGE_RUN_TYPE' => 'store'
        ],
        'staging.commerce.smartblinds.at' => [
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
                'dbname' => 'smartblinds_stag',
                'username' => 'smartblinds_stag',
                'password' => 'aUpX27ojnt23AQoo9bEWTb913Fm3CE5Z',
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
    'MAGE_MODE' => 'developer',
    'session' => [
        'save' => 'files'
    ],
    'cache' => [
        'frontend' => [
            'default' => [
                'id_prefix' => '476_'
            ],
            'page_cache' => [
                'id_prefix' => '476_'
            ]
        ],
        'allow_parallel_generation' => false
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
        'ec_cache' => 1,
        'amasty_shopby' => 1
    ],
    'downloadable_domains' => [
        'coulisse.loc'
    ],
    'install' => [
        'date' => 'Mon, 22 Mar 2021 16:59:42 +0000'
    ],
    'is_secure' => true,
    'http_auth' => [
        'user' => 'smart_dev',
        'pass' => 'smart_dev'
    ]
];
