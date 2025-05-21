<?php
namespace SemesterApparatus\Module\Config;

$config = [
    'controllers' => [
        'factories' => [
            'SemesterApparatus\Controller\MyResearchController' => 'SemesterApparatus\Controller\MyResearchControllerFactory',
        ],
        'aliases' => [
            'MyResearch' => 'SemesterApparatus\Controller\MyResearchController',
            'myresearch' => 'SemesterApparatus\Controller\MyResearchController',
        ],
    ],
    'service_manager' => [
        'allow_override' => true,
        'factories' => [
            'SemesterApparatus\Db\Row\UserList' => 'VuFind\Db\Row\RowGatewayFactory',
            'SemesterApparatus\Favorites\FavoritesService' => 'SemesterApparatus\Favorites\FavoritesServiceFactory',
        ],
    ],
    'vufind' => [
        'plugin_managers' => [
            'db_row' => [
                'factories' => [
                    'SemesterApparatus\Db\Row\Resource' => 'VuFind\Db\Row\RowGatewayFactory',
                    'SemesterApparatus\Db\Row\User' => 'SemesterApparatus\Db\Row\UserFactory',
                    'SemesterApparatus\Db\Row\UserList' => 'SemesterApparatus\Db\Row\UserListFactory',
                    'SemesterApparatus\Db\Row\UserResource' => 'VuFind\Db\Row\RowGatewayFactory',
                ],
                'aliases' => [
                    'resource' => 'SemesterApparatus\Db\Row\Resource',
                    'user' => 'SemesterApparatus\Db\Row\User',
                    'userlist' => 'SemesterApparatus\Db\Row\UserList',
                    'userresource' => 'SemesterApparatus\Db\Row\UserResource',
                ],
            ],
            'db_table' => [
                'factories' => [
                    'SemesterApparatus\Db\Table\Resource' => 'SemesterApparatus\Db\Table\ResourceFactory',
                    'SemesterApparatus\Db\Table\User' => 'SemesterApparatus\Db\Table\UserFactory',
                    'SemesterApparatus\Db\Table\UserList' => 'SemesterApparatus\Db\Table\UserListFactory',
                    'SemesterApparatus\Db\Table\UserResource' => 'VuFind\Db\Table\GatewayFactory',
                ],
                'aliases' => [
                    'resource' => 'SemesterApparatus\Db\Table\Resource',
                    'user' => 'SemesterApparatus\Db\Table\User',
                    'userlist' => 'SemesterApparatus\Db\Table\UserList',
                    'userresource' => 'SemesterApparatus\Db\Table\UserResource',
                ],
            ],
        ],
    ],
];

return $config;
