<?php

namespace Distill\Module;

use Distill\Application;

class CliErrorModule implements ModuleInterface
{
    public function bootstrapModule(Application $application)
    {
        $application->on('Application.PostRoute', [$this, 'handleNoRouteMatch']);
        $application->on('Application.Error', [$this, 'handleException']);
    }

    public function handleNoRouteMatch($routeMatch)
    {
        if (php_sapi_name() != 'cli') {
            return;
        }
        if (!$routeMatch) {
            echo 'Command not found' . PHP_EOL;
            exit(-1);
        }
    }

    public function handleError(\Exception $exception)
    {
        var_dump($exception);
        exit(-1);
    }

}
