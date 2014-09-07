<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:31 AM
 */

namespace Distill\Test\Router;

use Distill\Router\CliRoute;

class CliRouteTest extends \PHPUnit_Framework_TestCase
{
    public function testMatch()
    {
        $cliRoute = new CliRoute('$ hi', function () { return true; });
        $this->assertEquals([], $cliRoute->match(['argv' => ['$', 'hi']]));

        $cliRoute = new CliRoute('$ hi :name', function () { return true; });
        $this->assertEquals(['name' => 'ralph'], $cliRoute->match(['argv' => ['$', 'hi', 'ralph']]));
    }
}
