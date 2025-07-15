<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()
        ->set('locale', 'en_US')
    ;

    $services = $containerConfigurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
    ;

    // Load Behat contexts from their respective bounded contexts
    $services->load('App\\Tests\\BlogContext\\Behat\\', __DIR__.'/../tests/BlogContext/Behat/');
    $services->load('App\\Tests\\Shared\\Behat\\', __DIR__.'/../tests/Shared/Behat/');
};
