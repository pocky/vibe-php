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
                'items_per_page' => 20,
                'maximum_items_per_page' => 100,
                'items_per_page_parameter_name' => 'itemsPerPage',
                'client_enabled' => true,
                'client_items_per_page' => true,
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
            ],
        ],
    ]);

    if ('test' === $containerConfigurator->env()) {
        $containerConfigurator->extension('api_platform', [
            'eager_loading' => [
                'enabled' => false,
            ],
            'enable_docs' => false,
            'enable_entrypoint' => false,
            'enable_swagger' => false,
            'enable_swagger_ui' => false,
            'enable_re_doc' => false,
        ]);
    }

    if ('dev' === $containerConfigurator->env()) {
        $containerConfigurator->extension('api_platform', [
            'eager_loading' => [
                'enabled' => false,
            ],
        ]);
    }

    if ('prod' === $containerConfigurator->env()) {
        $containerConfigurator->extension('api_platform', [
            'eager_loading' => [
                'enabled' => true,
                'fetch_partial' => false,
                'max_joins' => 30,
                'force_eager' => true,
            ],
            'enable_docs' => false,
            'enable_entrypoint' => false,
            'enable_swagger' => false,
            'enable_swagger_ui' => false,
            'enable_re_doc' => false,
        ]);
    }
};
