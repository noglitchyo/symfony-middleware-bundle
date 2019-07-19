<?php declare(strict_types=1);

namespace NoGlitchYo\MiddlewareBundle\Tests\Unit\DependencyInjection;

use NoGlitchYo\MiddlewareBundle\DependencyInjection\Configuration;
use PHPStan\Testing\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    protected static $minimalConfig = [
        'handlers' => [
            'handlerIdentifier' => [
                'condition' => [
                    'routeName' => 'myRouteName',
                    'routePath' => '/route/path',
                ],
            ],
        ],
    ];

    public function testHandlers()
    {
        $this->markTestSkipped('Not implemented yet');
        $config = [
            'handlers' => [
                'handlerIdentifier' => [
                    'condition' => [
                        'routeName' => 'myRouteName',
                        'routePath' => '/route/path',
                    ],
                ],
            ],
        ];

        $processor     = new Processor();
        $configuration = new Configuration([], []);
        $processor->processConfiguration($configuration, [$config]);
    }
}
