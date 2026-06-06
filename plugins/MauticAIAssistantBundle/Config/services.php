<?php

use MauticPlugin\MauticAIAssistantBundle\Controller\AIChatController;
use MauticPlugin\MauticAIAssistantBundle\EventListener\AssetsSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(AssetsSubscriber::class);

    $services->set(AIChatController::class)
        ->tag('controller.service_arguments');
};
