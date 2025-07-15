<?php

declare(strict_types=1);

use Behat\Config\Config;
use Behat\Config\Extension;
use Behat\Config\Filter\TagFilter;
use Behat\Config\Formatter\PrettyFormatter;
use Behat\Config\Profile;
use Behat\MinkExtension\ServiceContainer\MinkExtension;
use Behat\Testwork\Output\Printer\Factory\OutputFactory;
use FriendsOfBehat\SymfonyExtension\ServiceContainer\SymfonyExtension;

use App\Tests\Behat\Context\BlogApiContext;
use App\Tests\Behat\Context\Hook\DoctrineORMContext;
use Behat\Config\Suite;

$profile = (new Profile('default'))
    ->withFormatter(
        (new PrettyFormatter(paths: false))
            ->withOutputVerbosity(OutputFactory::VERBOSITY_VERBOSE)
    )
    ->withFilter(new TagFilter('~@todo'))
    ->withSuite(
        (new Suite('blog'))
            ->withPaths('features/blog')
            ->withContexts(
                BlogApiContext::class,
                DoctrineORMContext::class,
            )
    )

    # Extensions
    ->withExtension(new Extension(FriendsOfBehat\MinkDebugExtension\ServiceContainer\MinkDebugExtension::class, [
        'directory' => 'etc/build',
        'clean_start' => true,
        'screenshot' => true,
    ]))
    ->withExtension(new Extension(FriendsOfBehat\VariadicExtension\ServiceContainer\VariadicExtension::class))
    ->withExtension(new Extension(Robertfausk\Behat\PantherExtension\ServiceContainer\PantherExtension::class))
    ->withExtension(new Extension(Behat\MinkExtension\ServiceContainer\MinkExtension::class, [
        'files_path' => '%paths.base%/tests/Resources/',
        'base_url' => 'https://127.0.0.1:8080',
        'default_session' => 'symfony',
        'javascript_session' => 'panther',
        'sessions' => [
            # Sessions
            'panther' => [
                'panther' => [
                    'options' => [
                        'browser' => 'chrome',
                        'webServerDir' => '%paths.base%/public',
                        'external_base_uri' => 'https://127.0.0.1:8080',
                    ],
                    'kernel_options' => [
                        'APP_ENV' => 'test',
                        'APP_DEBUG' => false,
                    ],
                    'manager_options' => [
                        'connection_timeout_in_ms' => 5000,
                        'request_timeout_in_ms' => 120000,
                        'capabilities' => [
                            'browserName' => 'chrome',
                            'browser' => 'chrome',
                            'acceptSslCerts' => true,
                            'acceptInsecureCerts' => true,
                            'unexpectedAlertBehaviour' => 'accept',
                        ],
                        'extra_capabilities' => [
                            'chromeOptions' => [
                                'args' => [
                                    '--window-size=2880,1800',
                                    '--headless',
                                    'start-fullscreen',
                                    'start-maximized',
                                    'no-sandbox',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'symfony' => [
                'symfony' => null,
            ],
        ],
    ]))
    ->withExtension(new Extension(SymfonyExtension::class, [
        'bootstrap' => 'tests/bootstrap.php',
    ]))
;

return (new Config())
    ->withProfile($profile)
;
