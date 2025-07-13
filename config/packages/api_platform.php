<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('api_platform', [
        'title' => '%env(APP_NAME)%',
        'version' => '%env(APP_VERSION)%',
        'description' => 'API for Domain-Driven Design application',
        'defaults' => [
            'stateless' => true,
            'cache_headers' => [
                'vary' => ['Content-Type', 'Authorization', 'Origin']
            ],
            'pagination' => [
                'enabled' => true,
                'page_parameter_name' => 'page',
                'enabled_parameter_name' => 'pagination',
                'items_per_page' => 30,
                'maximum_items_per_page' => 100,
            ],
        ],
        'patch_formats' => [
            'json' => [
                'application/merge-patch+json',
            ],
        ],
        'swagger' => [
            'versions' => [3],
            'api_keys' => [
                'apiKey' => [
                    'name' => 'Authorization',
                    'type' => 'header',
                ],
            ],
        ],
        'openapi' => [
            'contact' => [
                'name' => 'API Support',
                'email' => '%env(API_CONTACT_EMAIL)%',
            ],
            'license' => [
                'name' => 'EUPL-1.2',
                'url' => 'https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12',
            ],
        ],
        'mapping' => [
            'paths' => [
                '%kernel.project_dir%/src/BlogContext/UI/Api/Rest/Resource',
            ],
        ],
    ]);
};
