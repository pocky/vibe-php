<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('framework', [
        'validation' => [
            # Enables validator auto-mapping support.
            # For instance, basic validation constraints will be inferred from Doctrine's metadata.
            #'auto_mapping' => [
            #   'App\\ExampleContext\\Shared\\Infrastructure\\Persistence\\Doctrine\\ORM\\Entity\\' => [],
            #],
            'email_validation_mode' => 'html5',
        ],
    ]);

    if ('test' === $containerConfigurator->env()) {
        $containerConfigurator->extension('framework', [
            'validation' => [
                'not_compromised_password' => false,
            ],
        ]);
    }
};
