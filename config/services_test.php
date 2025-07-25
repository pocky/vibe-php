<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Behat\Behat\Context\Context;
use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;
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

    $services->instanceof(SymfonyPageInterface::class)->tag('test.behat_symfony_page');
    $services->instanceof(Context::class)->tag('test.behat_context');

    $services->load('App\\Tests\\Shared\\Behat\\', __DIR__.'/../tests/Shared/Behat/')
        ->exclude([
            __DIR__.'/../tests/Shared/Behat/Service/Formatter/StringInflector.php',
        ]);

    $services->alias(SessionFactoryInterface::class, 'session.factory');
};
