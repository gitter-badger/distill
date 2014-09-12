<?php

namespace Distill\Module;

use Distill\Application;

class CliHelpModule implements ModuleInterface
{
    protected $name;
    /** @var Application */
    protected $application;

    public function __construct($name = 'My Application')
    {
        $this->name = $name;
    }

    public function bootstrapModule(Application $application)
    {
        $application->on('Application.Initialize', [$this, 'initialize']);
        $application->on('Application.PreRoute', [$this, 'printBanner']);
        $application->on('Application.PostRoute', [$this, 'handleNoRouteMatch']);
        $application->on('Application.PostDispatch', function () { echo "\n"; });
        $application->on('Application.Error', [$this, 'handleException']);
    }

    public function initialize(Application $application)
    {
        $this->application = $application;
    }

    public function printBanner()
    {
        echo $this->name . PHP_EOL . PHP_EOL;
    }

    public function handleNoRouteMatch($routeMatch)
    {
        if (php_sapi_name() != 'cli') {
            return;
        }
        if (!$routeMatch) {
            echo 'Command not found' . PHP_EOL;
            $this->printHelp();
            exit(-1);
        }
    }

    public function handleError(\Exception $exception)
    {
        var_dump($exception);
        exit(-1);
    }

    public function printHelp()
    {
        $routes = $this->application->routes;
        foreach ($routes as $name => $route) {
            echo $name . ' ' . $route->assemble([]) . PHP_EOL;
        }
    }

}
