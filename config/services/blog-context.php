<?php

declare(strict_types=1);

use App\BlogContext\Application\Gateway\PublishArticle\Constraint\SeoReadyValidator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // SeoReadyValidator configuration
    $services->set(SeoReadyValidator::class)
        ->autowire()
        ->autoconfigure()
        ->tag('validator.constraint_validator');
};