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
        $application->on('Application.Error', [$this, 'handleError']);
    }

    public function initialize(Application $application)
    {
        $this->application = $application;
    }

    public function printBanner()
    {
        echo "$this->name\n\n";
    }

    public function handleNoRouteMatch($routeMatch)
    {
        if (php_sapi_name() != 'cli') {
            return;
        }
        if (!$routeMatch) {
            echo "Command not found\n";
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
        echo "\nRegistered commands:\n";
        $width = $i = -1;
        $routeTableRows = [];
        foreach ($routes as $name => $route) {
            $routeTableRows[++$i] = [$route->assemble([]), "The $name command"];
            if (strlen($routeTableRows[$i][0]) > $width) {
                $width = strlen($routeTableRows[$i][0]);
            }
        }
        foreach ($routeTableRows as $routeTableRow) {
            printf("    %-{$width}.{$width}s    %-30.30s\n", $routeTableRow[0], $routeTableRow[1]);
        }
    }

}
