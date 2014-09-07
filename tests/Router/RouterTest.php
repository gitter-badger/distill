<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:32 AM
 */

namespace Distill\Test\Router;


use Distill\Router\RouteMatch;
use Distill\Router\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Distill\Router\Router::setSourceData
     * @testdox unit test: test setSourceData()
     * @return Router
     */
    public function testSetSourceData()
    {
        $sourceData = new Router;
        $fluentTest = $sourceData->setSourceData(['method' => 'GET', 'uri' => '/']);
        $this->assertSame($fluentTest, $sourceData);
        return $sourceData;
    }

    /**
     * @covers Distill\Router\Router::getSourceData
     * @testdox unit test: test getSourceData()
     * @depends testSetSourceData
     */
    public function testGetSourceData(Router $sourceData)
    {
        $this->assertEquals(['method' => 'GET', 'uri' => '/'], $sourceData->getSourceData());
    }

    /**
     * @covers Distill\Router\Router::setLastRouteMatch
     * @testdox unit test: test setLastRouteMatch()
     * @return Router
     */
    public function testSetLastRouteMatch()
    {
        $lastRouteMatch = new Router;
        $fluentTest = $lastRouteMatch->setLastRouteMatch(new RouteMatch());
        $this->assertSame($fluentTest, $lastRouteMatch);
        return $lastRouteMatch;
    }

    /**
     * @covers Distill\Router\Router::getLastRouteMatch
     * @testdox unit test: test getLastRouteMatch()
     * @depends testSetLastRouteMatch
     */
    public function testGetLastRouteMatch(Router $lastRouteMatch)
    {
        $this->assertInstanceOf('Distill\Router\RouteMatch', $lastRouteMatch->getLastRouteMatch());
    }

    /**
     * @covers Distill\Router\Router::initializeSourceData
     * @testdox unit test: test initializeSourceData()
     */
    public function testInitializeSourceData()
    {
        $router = new Router;
        $router->initializeSourceData();
        $this->assertEquals(php_sapi_name(), $router->getSourceData()['sapi']);
    }

    /**
     * @covers Distill\Router\Router::getRouteStack
     * @testdox unit test: test getRouteStack()
     */
    public function testGetRouteStack()
    {
        $router = new Router;
        $this->assertInstanceOf('Distill\Router\RouteStack', $router->getRouteStack());
    }

    /**
     * @covers Distill\Router\Router::route
     * @testdox unit test: test route()
     */
    public function testRoute()
    {
        $router = new Router;
        $routeStack = $router->getRouteStack();
        $routeStack['home'] = ['GET /', 'foobar'];
        $router->setSourceData(['sapi' => 'http', 'method' => 'GET', 'uri' => '/']);
        $routeMatch = $router->route();
        $this->assertInstanceOf('Distill\Router\RouteMatch', $routeMatch);
        $this->assertEquals('foobar', $routeMatch->getRoute()->getDispatchable());
    }

    /**
     * @covers Distill\Router\Router::assembleMatch
     * @testdox unit test: test assembleMatch()
     */
    public function testAssembleMatch()
    {
        $this->markTestSkipped('Test not yet implemented');
    }

    /**
     * @covers Distill\Router\Router::assemble
     * @testdox unit test: test assemble()
     */
    public function testAssemble()
    {
        $this->markTestSkipped('Test not yet implemented');
    }

}
 