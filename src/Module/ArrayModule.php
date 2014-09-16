<?php

namespace Distill\Module;

use Distill\Application;
use Distill\Configuration;
use Distill\Router\Router;
use Distill\ServiceLocator\ServiceLocator;

class ArrayModule implements ModuleInterface
{
    /**@+
     * @var array
     */
    protected $routes = array();
    protected $services = array();
    protected $configuration = array();
    protected $callbacks = array();
    /**@-*/

    public function __construct(array $moduleArray = array())
    {
        foreach ($moduleArray as $section => $c) {
            if (!is_array($c)) {
                continue;
            }
            switch ($section) {
                case 'route':
                case 'routes':
                    $this->routes = $c;
                    break;
                case 'service':
                case 'services':
                    $this->services = $c;
                    break;
                case 'config':
                case 'configuration':
                    $this->configuration = $c;
                    break;
                case 'callback':
                case 'callbacks':
                    $this->callbacks = $c;
                    break;
            }
        }
    }

    public function bootstrapModule(Application $application)
    {
        $serviceLocator = $application->getServiceLocator();
        $configuration = $serviceLocator->get('Configuration');
        $router = $serviceLocator->get('Router');

        // configuration
        if ($this->configuration) {
            $configuration->merge($this->configuration);
        }

        // services
        if ($this->services) {
            foreach ($this->services as $name => $args) {
                if (is_object($args) && !$args instanceof \Closure) {
                    $serviceLocator->set($name, $args);
                } else {
                    $serviceLocator->set($name, $args[0], (isset($args[1]) ? $args[1] : null));
                }
            }
        }

        // routes
        if ($this->routes) {
            $routeStack = $router->getRouteStack();
            foreach ($this->routes as $routeName => $route) {
                $routeStack[$routeName] = $route;
            }
        }

        // callbacks
        if ($this->callbacks) {
            foreach ($this->callbacks as $callback) {
                $application->addCallback(
                    $callback[0],
                    $callback[1],
                    (isset($callback[2]) ? $callback[2] : 0)
                );
            }
        }
    }

}
