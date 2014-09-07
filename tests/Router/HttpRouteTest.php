<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:31 AM
 */

namespace Distill\Test\Router;


use Distill\Router\HttpRoute;

class HttpRouteTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $cliRoute = new HttpRoute('GET /', function () { return true; });
        $this->assertEquals([], $cliRoute->match(['uri' => '/', 'method' => 'GET']));

        $cliRoute = new HttpRoute('/:name', function () { return true; });
        $this->assertEquals(['name' => 'ralph'], $cliRoute->match(['uri' => '/ralph', 'method' => 'GET']));
    }
}
 