<?php
/**
 * Distill Framework
 * @link http://github.com/pframework
 * @license UNLICENSE http://unlicense.org/UNLICENSE
 * @copyright Public Domain
 * @author Ralph Schindler <ralph@ralphschindler.com>
 */

namespace Distill;

/**
 * @property bool $debug Is Debug Enabled?
 * @property bool $isDebug  Is Debug Enabled?
 * @property string $env Environment name
 * @property string $environment Environment name
 * @property string $path Application Path
 * @property Configuration $configuration Configuration service ArrayObject
 * @property Router\Router $router Router
 * @property Router\RouteStack $routes RouteStack
 * @property ServiceLocator\ServiceLocator $serviceLocator The service locator
 * @property ServiceLocator\ServiceLocator $services The service locator
 * @property Callback\CallbackCollection[] $callbacks The array of callbacks
 */
class Application implements \ArrayAccess
{
    /**@+
     * @var bool|string
     */
    protected $debug = false;
    protected $environment = 'production';
    protected $path = null;
    protected $isInitialized = false;
    /**@-*/

    /** @var ServiceLocator\ServiceLocator */
    protected $serviceLocator = null;

    /** @var Callback\CallbackCollection[] */
    protected $callbackCollections = array();

    /**
     * @param array|ServiceLocator\ServiceLocator config or service locator
     */
    public function __construct(/* configuration array or service locator instance */)
    {
        $arg1 = (func_num_args() > 0) ? func_get_arg(0) : null;

        $serviceLocator = ($arg1 instanceof ServiceLocator\ServiceLocator) ? $arg1 : new ServiceLocator\ServiceLocator;

        if (is_array($arg1)) {
            foreach ($arg1 as $n => $v) {
                switch (strtolower($n)) {
                    case 'debug': $this->setDebug($v); unset($arg1[$n]); break;
                    case 'env': case 'environment': $this->setEnvironment($v); unset($arg1[$n]); break;
                    case 'path': $this->setPath($v); unset($arg1[$n]); break;
                }
            }
            $serviceLocator->set('Configuration', new Configuration($arg1));
        }

        $this->bootstrap($serviceLocator);
    }

    /**
     * @return bool
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug($debug)
    {
        $this->debug = (bool) $debug;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function setEnvironment($environment)
    {
        if (!is_string($environment)) {
            throw new \InvalidArgumentException('environment must be a string');
        }
        $this->environment = $environment;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $path = realpath($path);
        if (!$path) {
            throw new \InvalidArgumentException('$path must be a real path on the filesystem');
        }
        $this->path = $path;
        return $this;
    }

    protected function bootstrap(ServiceLocator\ServiceLocator $sl)
    {
        $this->serviceLocator = $sl;

        // router
        ($sl->has('Router')) ? $sl->get('Router') : ($sl->set('Router', new Router\Router)->get('Router'));

        $sl->set('Application', $this);
        $sl->set('ServiceLocator', $sl);

        // config file application configuration
        if ($sl->has('Configuration')) {
            $configuration = $sl->get('Configuration');
            if (isset($configuration['application']) && is_array($configuration['application'])) {
                foreach ($configuration['application'] as $n => $v) {
                    $m = null;
                    switch ($n) {
                        case 'routes': foreach ($v as $a => $b) $this->addRoute($a, $b); break;
                        case 'services': foreach ($v as $a => $b) $this->addService($a, $b); break;
                        case 'callbacks': foreach ($v as $a => $b) $this->addCallback($a, $b); break;
                        case 'modules': foreach ($v as $a => $b) $this->register($b); break;
                        default: continue;
                    }
                }
            }
        } else {
            $sl->set('Configuration', new Configuration());
        }
    }

    /**
     * @return bool
     */
    public function initialize()
    {
        if ($this->isInitialized) {
            return false;
        }
        $this->call('Application.Initialize');
        $this->isInitialized = true;
        return true;
    }

    /**
     * @return mixed|null
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function run()
    {
        $this->initialize();

        /** @var $router Router\Router */
        $router = $this->serviceLocator->get('Router');

        $this->call('Application.PreRoute');

        try {
            $routeMatch = $router->route();
            if ($routeMatch instanceof Router\RouteMatch) {
                $this->serviceLocator->set('RouteMatch', $routeMatch);
            }
        } catch (\Exception $e) {
            if (isset($this->callbackCollections['Application.Error'])) {
                $this->call('Application.Error', ['exception' => $e]);
                return false;
            } else {
                throw $e;
            }
        }

        $this->call('Application.PostRoute', ['routeMatch' => $routeMatch]);

        if ($routeMatch == null) {
            /** @var $router Router\Router */
            $router = $this->serviceLocator->get('Router');
            $routeMatch = $router->getLastRouteMatch();

            if (!$routeMatch) {
                return false;
            }

        } elseif (!$routeMatch instanceof Router\RouteMatch) {
            throw new \InvalidArgumentException('Provided RouteMatch must be of type Distill\Router\RouteMatch');
        }

        $route = $routeMatch->getRoute();

        if (!$route instanceof Router\RouteInterface) {
            throw new \RuntimeException('Matched route must implement Distill\Router\RouteInterface');
        }

        $this->call('Application.PreDispatch', ['routeMatch' => $routeMatch, 'route' => $route]);

        $dispatchContext = null;
        try {
            $dispatchParams = $routeMatch->getParameters();
            $routeSource = $router->getSourceData();
            if ($routeSource['sapi'] == 'http') {
                $dispatchParams['__HTTP_URI__'] = $routeSource['uri'];
                $dispatchParams['__HTTP_METHOD__'] = $routeSource['method'];
            }
            $dispatchable = $route->getDispatchable();
            $return = $this->serviceLocator->invoke($dispatchable, $dispatchParams, $this, $actualDispatchable);
        } catch (\Exception $e) {
            if (isset($this->callbackCollections['Application.Error'])) {
                $this->call('Application.Error', ['exception' => $e]);
                return false;
            } else {
                throw $e;
            }
        }
        $this->call('Application.PostDispatch', ['dispatchable' => $actualDispatchable, 'return' => isset($return) ? $return : null]);

        return true;
    }

    public function on($name, $callback, $priority = 0)
    {
        return $this->addCallback($name, $callback, $priority);
    }

    /**
     * @param $name
     * @param array $parameters
     * @return false|Callback\CallbackContext
     */
    public function call($name, $parameters = array())
    {
        if (!isset($this->callbackCollections[$name])) {
            return false;
        }
        $context = $this->callbackCollections[$name]->getCallbackContext();
        if ($parameters) {
            $context->setParameters($parameters);
        }
        /** @var \Callable $callback */
        foreach (clone $this->callbackCollections[$name] as $callback) {
            $return = $this->serviceLocator->invoke($callback, $context, $this);
            if (!is_null($return)) {
                $context->pushReturn($return);
            }
        }
        return $context;
    }

    public function register($module)
    {
        if (is_array($module)) {
            $module = new Module\ArrayModule($module);
        } elseif (!$module instanceof Module\ModuleInterface) {
            throw new \InvalidArgumentException('Context but be an array or Distill\Module\ModuleInterface object');
        }

        $module->bootstrapModule($this);

        return $this;
    }

    public function addRoute($nameOrRouteSpec /*, $routeSpec */)
    {
        $funcArgs = func_get_args();
        $args = (is_array($nameOrRouteSpec)) ? array(null, ) : array($funcArgs[0], $funcArgs[1]);
        /** @var Router\RouteStack $routeStack */
        $routeStack = $this->serviceLocator->get('Router')->getRouteStack();
        $routeStack->offsetSet($args[0], $args[1]);
        return $this;
    }
    
    public function addService($name, $service)
    {
        $this->serviceLocator[$name] = $service;
        return $this;
    }

    public function addCallback($name, $callback, $priority = 0)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException('$name must be a string');
        }

        if (!isset($this->callbackCollections[$name])) {
            $this->callbackCollections[$name] = new Callback\CallbackCollection();
        }

        $this->callbackCollections[$name]->insert($callback, $priority);
        return $callback;
    }

    public function getCallbackCollection($name)
    {
        return (isset($this->callbackCollections[$name]) ? $this->callbackCollections[$name] : false);
    }

    /**
     * @param string|null $routeName
     * @param string|Router\RouteInterface $routeSpecification
     * @return Application|void
     */
    public function offsetSet($routeName, $routeSpecification)
    {
        /** @var Router\RouteStack $routeStack */
        $routeStack = $this->serviceLocator->get('Router')->getRouteStack();
        $routeStack[$routeName] = $routeSpecification;
        return $this;
    }

    /**
     * Get A Route
     * @param mixed $routeName
     * @return Router\RouteInterface
     */
    public function offsetGet($routeName)
    {
        $routeStack = $this->serviceLocator->get('Router')->getRouteStack();
        return $routeStack[$routeName];
    }

    /**
     * Does Route Exist?
     * @param mixed $routeName
     * @return bool
     */
    public function offsetExists($routeName)
    {
        $routeStack = $this->serviceLocator->get('Router')->getRouteStack();
        return isset($routeStack[$routeName]);
    }

    /**
     * Remove a Route
     * @param mixed $routeName
     */
    public function offsetUnset($routeName)
    {
        $routeStack = $this->serviceLocator->get('Router')->getRouteStack();
        unset($routeStack[$routeName]);
    }

    /**
     * @return ServiceLocator\ServiceLocator
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param $name
     * @return ServiceLocator\ServiceLocator|mixed
     * @throws \InvalidArgumentException
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'debug':
            case 'isdebug':
                return $this->debug;
            case 'env':
            case 'environment':
                return $this->environment;
            case 'path':
                return $this->path;
            case 'configuration':
            case 'config':
                return $this->serviceLocator->get('Configuration');
            case 'services':
            case 'servicelocator':
                return $this->serviceLocator;
            case 'router':
                return $this->serviceLocator->get('Router');
            case 'routes':
                return $this->serviceLocator->get('Router')->getRouteStack();
            case 'callbacks':
                $callbackCollections = [];
                foreach ($this->callbackCollections as $callbackCollection) {
                    $callbackCollections[] = clone $callbackCollection;
                }
                return $callbackCollections;
            default:
                if ($this->serviceLocator->has($name)) {
                    return $this->serviceLocator->get($name);
                }
        }

        throw new \InvalidArgumentException(
            $name . ' is not a valid property in the application object or a valid service in the ServiceLocator'
        );
    }

}
