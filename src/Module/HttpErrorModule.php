<?php

namespace Distill\Module;

use Distill\Application;

class HttpErrorModule implements ModuleInterface
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
            http_response_code(404);
            echo 'Page not found.';
            exit(-1);
        }
    }

    public function handleException(\Exception $exception)
    {
        http_response_code(500);
        echo 'An internal application error has occurred.';
        exit(-1);
    }
}
