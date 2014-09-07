<?php
/**
 * Distill Framework
 * @link http://github.com/pframework
 * @license UNLICENSE http://unlicense.org/UNLICENSE
 * @copyright Public Domain
 * @author Ralph Schindler <ralph@ralphschindler.com>
 */

namespace Distill\Router;

/**
 * @property $routes Router\RouteStack
 */
class Router
{
    const ASSEMBLE_USE_LAST_ROUTE_MATCH = null;

    /**
     * @var RouteStack
     */
    protected $routeStack = null;
    protected $sourceData = null;
    protected $routeMatchPrototype = null;
    protected $lastRouteMatch = null;

    /**
     * @param array|RouteStack $routes
     * @param $source
     * @param RouteMatch $routeMatchPrototype
     */
    public function __construct($routes = array(), RouteMatch $routeMatchPrototype = null)
    {
        if ($routes instanceof RouteStack) {
            $this->routeStack = $routes;
        } else {
            $this->routeStack = new RouteStack($routes);
        }

        $this->routeMatchPrototype = ($routeMatchPrototype) ?: new RouteMatch;
    }

    /**
     * @param array $sourceData
     */
    public function initializeSourceData(array $sourceData = array())
    {
        if (php_sapi_name() == 'cli') {
            $sourceData['sapi'] = 'cli';
            $sourceData['argv'] = array_splice($_SERVER['argv'], 1);
        } else {
            $sourceData['sapi'] = 'http';
            $sourceData['method'] = $_SERVER['REQUEST_METHOD'];
            $sourceData['uri'] = $_SERVER['REQUEST_URI'];
        }
        $this->sourceData = $sourceData;
    }

    public function getRouteStack()
    {
        return $this->routeStack;
    }

    public function getSourceData()
    {
        return $this->sourceData;
    }

    public function setSourceData(array $source)
    {
        $this->sourceData = $source;
        return $this;
    }

    /**
     * @return RouteMatch|null
     */
    public function getLastRouteMatch()
    {
        return $this->lastRouteMatch;
    }

    public function setLastRouteMatch($lastRouteMatch)
    {
        $this->lastRouteMatch = $lastRouteMatch;
        return $this;
    }

    public function route()
    {
        if (!$this->sourceData) {
            $this->initializeSourceData();
        }

        /** @var $route RouteInterface */
        foreach ($this->routeStack as $name => $route) {
            $parameters = $route->match($this->sourceData);
            if ($parameters !== false) {
                $routeMatch = clone $this->routeMatchPrototype;
                $routeMatch->setName($name);
                $routeMatch->setRoute($route);
                $routeMatch->setParameters($parameters);
                $this->setLastRouteMatch($routeMatch);
                return $routeMatch;
            }
        }

        return false;
    }

    public function assembleMatch($parameters = array())
    {
        return $this->assemble(self::ASSEMBLE_USE_LAST_ROUTE_MATCH, $parameters);
    }

    public function assemble($routeName, array $parameters = array())
    {
        if ($routeName == self::ASSEMBLE_USE_LAST_ROUTE_MATCH) {
            $routeName = $this->getLastRouteMatch()->getName();
        }

        /** @var $route RouteInterface */
        $route = $this->routeStack[$routeName];
        return $route->assemble($parameters);
    }

    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'routematch':
            case 'lastroutematch':
                return $this->lastRouteMatch;
            case 'routes':
                return $this->routeStack;
            case 'routestack':
                return $this->routeStack;
        }
        throw new \InvalidArgumentException(
            $name . ' is not a valid magic property.'
        );
    }

}
