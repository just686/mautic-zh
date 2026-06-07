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
            'mautic_registration_terms' => [
                'path'       => '/register/terms',
                'controller' => RegistrationController::class . '::termsAction',
                'method'     => 'GET',
            ],
            'mautic_registration_privacy' => [
                'path'       => '/register/privacy',
                'controller' => RegistrationController::class . '::privacyAction',
                'method'     => 'GET',
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
