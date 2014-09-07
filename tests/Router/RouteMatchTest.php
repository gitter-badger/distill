<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:31 AM
 */

namespace Distill\Test\Router;

use Distill\Router\HttpRoute;
use Distill\Router\RouteMatch;

class RouteMatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Distill\Router\RouteMatch::setName
     * @testdox unit test: test setName()
     * @return RouteMatch
     */
    public function testSetName()
    {
        $name = new RouteMatch;
        $fluentTest = $name->setName('foo');
        $this->assertSame($fluentTest, $name);
        return $name;
    }

    /**
     * @covers Distill\Router\RouteMatch::getName
     * @testdox unit test: test getName()
     * @depends testSetName
     */
    public function testGetName(RouteMatch $name)
    {
        $this->assertEquals('foo', $name->getName());
    }

    /**
     * @covers Distill\Router\RouteMatch::setRoute
     * @testdox unit test: test setRoute()
     * @return RouteMatch
     */
    public function testSetRoute()
    {
        $route = new RouteMatch;
        $fluentTest = $route->setRoute(new HttpRoute('/', function () {}));
        $this->assertSame($fluentTest, $route);
        return $route;
    }

    /**
     * @covers Distill\Router\RouteMatch::getRoute
     * @testdox unit test: test getRoute()
     * @depends testSetRoute
     */
    public function testGetRoute(RouteMatch $route)
    {
        $this->assertInstanceOf('Distill\Router\HttpRoute', $route->getRoute());
    }

    /**
     * @covers Distill\Router\RouteMatch::setParameters
     * @testdox unit test: test setParameters()
     * @return RouteMatch
     */
    public function testSetParameters()
    {
        $parameters = new RouteMatch;
        $fluentTest = $parameters->setParameters(['name' => 'foo']);
        $this->assertSame($fluentTest, $parameters);
        return $parameters;
    }

    /**
     * @covers Distill\Router\RouteMatch::getParameters
     * @testdox unit test: test getParameters()
     * @depends testSetParameters
     */
    public function testGetParameters(RouteMatch $parameters)
    {
        $this->assertEquals(['name' => 'foo'], $parameters->getParameters());
    }


}
 