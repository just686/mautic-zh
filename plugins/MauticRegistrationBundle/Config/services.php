<?php

use MauticPlugin\MauticRegistrationBundle\Controller\RegistrationController;
use MauticPlugin\MauticRegistrationBundle\Controller\SecurityOverrideController;
use MauticPlugin\MauticRegistrationBundle\EventListener\ConfigAccessListener;
use MauticPlugin\MauticRegistrationBundle\EventListener\LoginPageListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(RegistrationController::class)
        ->tag('controller.service_arguments');

    $services->set(SecurityOverrideController::class)
        ->tag('controller.service_arguments');

    $services->set(ConfigAccessListener::class);
    $services->set(LoginPageListener::class);
};
