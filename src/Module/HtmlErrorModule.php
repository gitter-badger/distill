<?php

namespace Distill\Module;

use Distill\Application;

class HtmlErrorModule implements ModuleInterface
{
    public function bootstrapModule(Application $application)
    {
        $application->on('Application.PostRoute', [$this, 'handleNoRouteMatch']);
        $application->on('Application.Error', [$this, 'handleException']);
    }

    public function handleNoRouteMatch($routeMatch)
    {
        if (php_sapi_name() == 'cli') {
            return;
        }
        if (!$routeMatch) {
            echo 'Missing route.';
            exit(-1);
        }
    }

    public function handleException(\Exception $exception)
    {
        echo '<pre>';
        echo $exception->getMessage();
        var_dump($exception->getTraceAsString());
        exit(-1);
    }
}
