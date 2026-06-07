<?php

use MauticPlugin\MauticRegistrationBundle\Controller\RegistrationController;
use MauticPlugin\MauticRegistrationBundle\Controller\SecurityOverrideController;

return [
    'name'        => '用户注册',
    'description' => '海客平台用户自助注册系统',
    'version'     => '1.0.0',
    'author'      => 'Gelab',
    'routes'      => [
        'public' => [
            'mautic_registration_form' => [
                'path'       => '/register',
                'controller' => RegistrationController::class . '::registerAction',
                'method'     => ['GET', 'POST'],
            ],
        ],
        'main' => [
            'mautic_config_guard' => [
                'path'       => '/config/guard',
                'controller' => SecurityOverrideController::class . '::guardAction',
            ],
        ],
    ],
];
