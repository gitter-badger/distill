<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:28 AM
 */

namespace Distill\Test;

use Distill\Application;
use Distill\Callback\CallbackCollection;
use Distill\Router\CliRoute;
use Distill\Router\RouteMatch;
use Distill\Router\Router;
use Distill\ServiceLocator\ServiceLocator;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $app = new Application(['foo' => 'bar']);
        $this->assertEquals('bar', $app->configuration['foo']);

        $app = new Application(['debug' => true]);
        $this->assertTrue($app->isDebug());
    }

    /**
     * @covers Distill\Application::setDebug
     * @testdox unit test: test setDebug()
     */
    public function testSetDebug()
    {
        $app = new Application();
        $this->assertFalse($app->isDebug());
        $app->setDebug(true);
        $this->assertTrue($app->isDebug());
        $app->setDebug(false);
        $this->assertFalse($app->isDebug());
    }

    /**
     * @covers Distill\Application::isDebug
     * @testdox unit test: test isDebug()
     */
    public function testIsDebug()
    {
        $app = new Application();
        $this->assertFalse($app->isDebug());
        $app->setDebug(true);
        $this->assertTrue($app->isDebug());
    }

    /**
     * @covers Distill\Application::setEnvironment
     * @testdox unit test: test setEnvironment()
     */
    public function testSetEnvironment()
    {
        $app = new Application();
        $this->assertSame($app, $app->setEnvironment('dev'));
    }

    /**
     * @covers Distill\Application::getEnvironment
     * @testdox unit test: test getEnvironment()
     */
    public function testGetEnvironment()
    {
        $app = new Application();
        $this->assertEquals('production', $app->getEnvironment());

        $app->setEnvironment('dev');
        $this->assertEquals('dev', $app->getEnvironment());
    }

    /**
     * @covers Distill\Application::initialize
     * @testdox unit test: test initialize()
     */
    public function testInitialize()
    {
        /** @var Application|\PhpUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMock('Distill\Application', ['call']);
        $app->expects($this->once())->method('call')->with('Application.Initialize');
        $this->assertSame($app, $app->initialize());
    }

    /**
     * @covers Distill\Application::run
     * @testdox unit test: test run()
     */
    public function testRun()
    {
        $dispatchableWasCalled = false;

        $router = $this->getMock('Distill\Router\Router', ['route']);
        $routeMatch = new RouteMatch();
        $routeMatch->setName('foo');
        $routeMatch->setRoute(
            new CliRoute('', function () use (&$dispatchableWasCalled) {
                $dispatchableWasCalled = true;
            })
        );
        $routeMatch->setParameters([]);
        $router->expects($this->any())->method('route')->willReturn($routeMatch);

        $sl = new ServiceLocator();
        $sl->set('Router', $router);

        /** @var Application|\PhpUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMock('Distill\Application', ['initialize', 'call'], [$sl]);

        $app->expects($this->at(1))->method('call')->with('Application.PreRoute');
        $app->expects($this->at(2))->method('call')->with('Application.PostRoute');
        $app->expects($this->at(3))->method('call')->with('Application.PreDispatch');
        $app->expects($this->at(4))->method('call')->with('Application.PostDispatch');

        $app->run();

        $this->assertTrue($dispatchableWasCalled);
    }

    /**
     * @covers Distill\Application::on
     * @testdox unit test: test on()
     */
    public function testOn()
    {
        /** @var Application|\PhpUnit_Framework_MockObject_MockObject $app */
        $app = $this->getMock('Distill\Application', ['addCallback']);
        $func = function () {};
        $app->expects($this->once())->method('addCallback')->with('Foo', $func)->willReturn($func);
        $callback = $app->on('Foo', $func);
        $this->assertSame($func, $callback);
    }

    /**
     * @covers Distill\Application::call
     * @testdox unit test: test call()
     */
    public function testCall()
    {
        $app = new Application();
        $this->assertFalse($app->call('foo'));

        $called = false;
        $app->on('foo', function () use (&$called) { $called = true; });
        $result = $app->call('foo');
        $this->assertInstanceOf('Distill\Callback\CallbackContext', $result);
        $this->assertTrue($called);
    }

    /**
     * @covers Distill\Application::register
     * @testdox unit test: test register()
     */
    public function testRegister()
    {
        $app = new Application();
        $app->register([
            'callbacks' => [
                ['foo', 'foobar']
            ]
        ]);
        $cc = $app->getCallbackCollection('foo');
        $this->assertEquals('foobar', $cc->current());
    }

    /**
     * @covers Distill\Application::addRoute
     * @testdox unit test: test addRoute()
     */
    public function testAddRoute()
    {
        $app = new Application();
        $app->addRoute('foo', ['GET /foo', 'mydispatchable']);
        $routeStack = $app->routes;
        /** @var \Distill\Router\HttpRoute $route */
        $route = $routeStack['foo'];
        $this->assertEquals('GET /foo', $route->getSpecification());
        $this->assertEquals('mydispatchable', $route->getDispatchable());
    }

    /**
     * @covers Distill\Application::addService
     * @testdox unit test: test addService()
     */
    public function testAddService()
    {
        $app = new Application();
        $app->addService('foo', $s = function () {});
        $services = $app->services;
        $this->assertTrue($services->has('foo'));
    }

    /**
     * @covers Distill\Application::addCallback
     * @testdox unit test: test addCallback()
     */
    public function testAddCallback()
    {
        $app = new Application();
        $app->addCallback('foo', $f = function () {});
        $this->assertContains($f, $app->getCallbackCollection('foo'));
    }

    /**
     * @covers Distill\Application::getCallbackCollection
     * @testdox unit test: test getCallbackCollection()
     */
    public function testGetCallbackCollection()
    {
        $app = new Application();
        $app->addCallback('foo', $f = function () {});
        $this->assertInstanceOf('Distill\Callback\CallbackCollection', $app->getCallbackCollection('foo'));
    }

    /**
     * @covers Distill\Application::offsetSet
     * @testdox unit test: test offsetSet()
     */
    public function testOffsetSet()
    {
        $routeStack = $this->getMock('Distill\Router\RouteStack', ['offsetSet']);
        $routeStack->expects($this->once())->method('offsetSet')->with('foo', 'bar');
        $router = new Router($routeStack);

        $sl = new ServiceLocator();
        $sl->set('Router', $router);

        $app = new Application($sl);
        $app->offsetSet('foo', 'bar');
    }

    /**
     * @covers Distill\Application::offsetGet
     * @testdox unit test: test offsetGet()
     */
    public function testOffsetGet()
    {
        $routeStack = $this->getMock('Distill\Router\RouteStack', ['offsetGet']);
        $routeStack->expects($this->once())->method('offsetGet')->with('foo');
        $router = new Router($routeStack);

        $sl = new ServiceLocator();
        $sl->set('Router', $router);

        $app = new Application($sl);
        $app->offsetGet('foo');
    }

    /**
     * @covers Distill\Application::offsetExists
     * @testdox unit test: test offsetExists()
     */
    public function testOffsetExists()
    {
        $routeStack = $this->getMock('Distill\Router\RouteStack', ['offsetExists']);
        $routeStack->expects($this->once())->method('offsetExists')->with('foo');
        $router = new Router($routeStack);

        $sl = new ServiceLocator();
        $sl->set('Router', $router);

        $app = new Application($sl);
        $app->offsetExists('foo');

    }

    /**
     * @covers Distill\Application::offsetUnset
     * @testdox unit test: test offsetUnset()
     */
    public function testOffsetUnset()
    {
        $routeStack = $this->getMock('Distill\Router\RouteStack', ['offsetUnset']);
        $routeStack->expects($this->once())->method('offsetUnset')->with('foo');
        $router = new Router($routeStack);

        $sl = new ServiceLocator();
        $sl->set('Router', $router);

        $app = new Application($sl);
        $app->offsetUnset('foo');

    }

    /**
     * @covers Distill\Application::getServiceLocator
     * @testdox unit test: test getServiceLocator()
     */
    public function testGetServiceLocator()
    {
        $app = new Application();
        $this->assertInstanceOf('Distill\ServiceLocator\ServiceLocator', $app->getServiceLocator());
    }

}
