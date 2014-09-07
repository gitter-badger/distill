<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:32 AM
 */

namespace Distill\Test\Router;


use Distill\Router\RouteStack;

class RouteStackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Distill\Router\RouteStack::addRoutes
     * @testdox unit test: test addRoutes()
     */
    public function testAddRoutes()
    {
        $routeStack = new RouteStack();
        $routeStack->addRoutes([
            ['GET /', function () {}]
        ]);
        $this->assertCount(1, $routeStack);
    }

    /**
     * @covers Distill\Router\RouteStack::offsetSet
     * @testdox unit test: test offsetSet()
     */
    public function testOffsetSet()
    {
        $routeStack = new RouteStack();
        $routeStack->offsetSet('home', ['GET /', function () {}]);
        $this->assertCount(1, $routeStack);
    }

    /**
     * @covers Distill\Router\RouteStack::offsetGet
     * @testdox unit test: test offsetGet()
     */
    public function testOffsetGet()
    {
        $routeStack = new RouteStack();
        $routeStack->offsetSet('home', ['GET /', function () {}]);
        $this->assertInstanceOf('Distill\Router\HttpRoute', $routeStack->offsetGet('home'));
    }

    /**
     * @covers Distill\Router\RouteStack::offsetUnset
     * @testdox unit test: test offsetUnset()
     */
    public function testOffsetUnset()
    {
        $routeStack = new RouteStack();
        $routeStack->offsetSet('home', ['GET /', function () {}]);
        $routeStack->offsetUnset('home');
        $this->assertCount(0, $routeStack);
    }

    /**
     * @covers Distill\Router\RouteStack::offsetExists
     * @testdox unit test: test offsetExists()
     */
    public function testOffsetExists()
    {
        $routeStack = new RouteStack();
        $routeStack->offsetSet('home', ['GET /', function () {}]);
        $this->assertTrue($routeStack->offsetExists('home'));
    }

    /**
     * @covers Distill\Router\RouteStack::getIterator
     * @testdox unit test: test getIterator()
     */
    public function testGetIterator()
    {
        $routeStack = new RouteStack();
        $routeStack->offsetSet('home', ['GET /', function () {}]);
        $this->assertInstanceOf('ArrayIterator', $routeStack->getIterator());
    }


}
 