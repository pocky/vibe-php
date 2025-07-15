<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
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
};