<?php

namespace Distill\Test\Router;

use Distill\Router\HttpRoute;

class HttpRouteTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider matchDataProvider
     */
    public function testMatch($ctorArgs, $matchParams, $equalTo)
    {
        $ctorArgs = (array) $ctorArgs;

        $httpRoute = new HttpRoute(
            $ctorArgs[0],
            function () { return true; },
            (isset($ctorArgs[1])) ? $ctorArgs[1] : [],
            (isset($ctorArgs[2])) ? $ctorArgs[2] : [],
            (isset($ctorArgs[3])) ? $ctorArgs[3] : true
        );
        $output = $httpRoute->match($matchParams);
        $this->assertEquals($equalTo, $output);
    }

    public function matchDataProvider()
    {
        return [
            // match root
            [
                'GET /',
                ['uri' => '/', 'method' => 'GET'],
                []
            ],
            // match root with required param, provided
            [
                '/:name',
                ['uri' => '/ralph', 'method' => 'GET'],
                ['name' => 'ralph']
            ],
            // match root with required param, not provided
            [
                '/:name',
                ['uri' => '/', 'method' => 'GET'],
                false
            ],
            // match root with optional param, provided
            [
                '/[:name]',
                ['uri' => '/ralph', 'method' => 'GET'],
                ['name' => 'ralph']
            ],
            // match root with optional param, not provided
            [
                '/[:name]',
                ['uri' => '/', 'method' => 'GET'],
                []
            ],
            // match literal with optional param, not provided
            [
                '/hello[/:name]',
                ['uri' => '/hello', 'method' => 'GET'],
                []
            ],
            // match literal with optional param, provided
            [
                '/hello[/:name]',
                ['uri' => '/hello/ralph', 'method' => 'GET'],
                ['name' => 'ralph']
            ],
            // match literal and required param, not provided
            [
                '/hello/:name',
                ['uri' => '/hello', 'method' => 'GET'],
                false
            ],
            // match optional param with validation, provided
            [
                '/hello[/:name#ralph|joe#]',
                ['uri' => '/hello/joe', 'method' => 'GET'],
                ['name' => 'joe']
            ],
            // match optional param with validation, provided, but fails validation
            [
                '/hello[/:name#ralph|joe#]',
                ['uri' => '/hello/foo', 'method' => 'GET'],
                false
            ],
            // match optional parameter with validation, not provided
            [
                '/hello[:id#\d#]',
                ['uri' => '/hello', 'method' => 'GET'],
                []
            ],
            // match required param with validation, provided
            [
                '/hello/:name#ralph|joe#',
                ['uri' => '/hello/ralph', 'method' => 'GET'],
                ['name' => 'ralph']
            ],
            // match required param with validation, not provided
            [
                '/hello/:name#ralph|joe#',
                ['uri' => '/hello', 'method' => 'GET'],
                false
            ],
            // match required param with regex validation, provided
            [
                '/hello/:id#\d#',
                ['uri' => '/hello/5', 'method' => 'GET'],
                ['id' => '5']
            ],
            // match required param with regex validation, provided
            [
                '/hello/:id#\d#',
                ['uri' => '/hello/thing', 'method' => 'GET'],
                false
            ],
            // match required param with regex validation, provided
            [
                '/hello/:id#\d#',
                ['uri' => '/hello/456ggg', 'method' => 'GET'],
                false
            ],
            // match literal, no implied trailing slash
            [
                ['/hello', [], [], false],
                ['uri' => '/hello/', 'method' => 'GET'],
                false
            ],
            // match literal with optional parameter, no implied trailing slash
            [
                ['/hello[/:name]', [], [], false],
                ['uri' => '/hello/', 'method' => 'GET'],
                false
            ],
            // match literal with optional parameter, no implied trailing slash
            [
                ['/hello[/:name]', [], [], false],
                ['uri' => '/hello/ralph/', 'method' => 'GET'],
                false
            ],
            // match literal with optional param, with default
            [
                ['/hello[/:name]', ['name' => 'ralph'], []],
                ['uri' => '/hello/', 'method' => 'GET'],
                ['name' => 'ralph']
            ],
            // match literal with optional param, with default
            [
                ['/hello/:name', ['name' => 'ralph'], []],
                ['uri' => '/hello/', 'method' => 'GET'],
                false
            ],
            // match literal with required param, with provided validator
            [
                ['/hello/:id', [], ['id' => ['#\d#']]],
                ['uri' => '/hello/foo', 'method' => 'GET'],
                false
            ]
        ];
    }
}
