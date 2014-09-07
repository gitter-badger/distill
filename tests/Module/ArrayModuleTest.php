<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:29 AM
 */

namespace Distill\Test\Module;

use Distill\Application;
use Distill\Module\ArrayModule;

class ArrayModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testBootstrapModule()
    {
        $array = [
            'routes' => [
                'home' => ['GET /', function () {}]
            ],
            'services' => [
                'foo' => [function() {}]
            ],
            'configuration' => [
                'bar' => 'baz'
            ],
            'callbacks' => [
                ['Application.PostRoute', function () {}]
            ]
        ];

        $arrayModule = new ArrayModule($array);

        $application = new Application();
        $arrayModule->bootstrapModule($application);

        // configuration
        $this->assertSame($array['configuration']['bar'], $application->configuration['bar']);

        // router
        $homeRoute = $application->routes['home'];
        $this->assertSame($array['routes']['home'][0], $homeRoute->getSpecification());
        $this->assertSame($array['routes']['home'][1], $homeRoute->getDispatchable());

        // services
        $this->assertSame($array['services']['foo'][0], $application->getServiceLocator()->getFactory('foo'));

        // callbacks
        $callbacks = iterator_to_array($application->getCallbackCollection('Application.PostRoute'));
        $this->assertSame($array['callbacks'][0][1], $callbacks[0]);

    }
}
