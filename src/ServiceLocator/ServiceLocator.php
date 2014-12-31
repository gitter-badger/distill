<?php
/**
 * Distill Framework
 * @link http://github.com/pframework
 * @license UNLICENSE http://unlicense.org/UNLICENSE
 * @copyright Public Domain
 * @author Ralph Schindler <ralph@ralphschindler.com>
 */

namespace Distill\ServiceLocator;

class ServiceLocator implements \ArrayAccess, \Countable
{
    /** @var mixed[] */
    protected $factories = [];

    /** @var string[] */
    protected $types = [];

    /** @var bool[] */
    protected $instances = [];

    /** @var bool[] */
    protected $modifiables = [];

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return (isset($this->factories[$name]) || isset($this->instances[$name]));
    }

    /**
     * @param $typeHint
     * @return bool
     */
    public function hasType($typeHint)
    {
        return (isset($this->types[strtolower($typeHint)]));
    }

    /**
     * @param $name
     * @param string|callable|object $service
     * @param string|string[] $type (Null if actual service)
     * @param bool $modifiable
     * @return ServiceLocator
     * @throws \InvalidArgumentException
     */
    public function set($name, $service, $type = null, $modifiable = false)
    {
        if (!is_string($name) || $name == '') {
            throw new \InvalidArgumentException('$name must be a string in ServiceLocator::set()');
        }
        if (isset($this->modifiables[$name]) && $this->modifiables[$name] === false) {
            throw new \InvalidArgumentException(
                'This service ' . $name . ' is already set and cannot be modified.'
            );
        }
        if (isset($this->instances[$name])) {
            throw new \InvalidArgumentException('An instance already exists for ' . $name . ' so it cannot be modified');
        }

        if (is_string($service) || $service instanceof \Closure) {
            $this->factories[$name] = $service;
        }

        if ($type) {
            foreach (((!is_array($type)) ? [$type] : $type) as $type) {
                $this->types[strtolower($type)] = $name;
            }
        }

        if (is_object($service) && !$service instanceof \Closure) {
            $this->instances[$name] = $service;
            $this->types[($type) ?: strtolower(get_class($service))] = $name;
        }

        $this->modifiables[$name] = $modifiable;
        return $this;
    }

    public function getFactory($name)
    {
        return $this->factories[$name];
    }

    /**
     * @param $name
     * @return mixed
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function get($name)
    {
        if (!isset($this->factories[$name]) && !isset($this->instances[$name])) {
            throw new \Exception('Service by name ' . $name . ' was not located in this ServiceLocator');
        }

        if (!isset($this->instances[$name])) {
            $this->instances[$name] = $this->getNew($name);
            if (!in_array($name, $this->types)) {
                $this->types[strtolower(get_class($this->instances[$name]))] = $name;
            }
        }
        return $this->instances[$name];
    }

    public function getNew($name)
    {
        static $depth = 0;
        static $allNames = array();

        if ($depth > 99) {
            throw new \RuntimeException(
                'Recursion detected when trying to resolve these services: ' . implode(', ', $allNames)
            );
        }

        $depth++;
        $allNames[] = $name;

        if (isset($this->factories[$name])) {
            $factory = $this->factories[$name];
            if (!is_callable($factory)) {
                $factory = $this->instantiate($factory);
            }
            $service = $this->invoke($factory, $this, $this);
        } elseif (isset($this->instances[$name])) {
            /** @var object $instance */
            $instance = $this->instances[$name];
            $service = clone $instance;
        } else {
            throw new \InvalidArgumentException('Cannot create instance by name ' . $name);
        }

        $depth--;
        return $service;
    }

    public function instantiate($instantiator, $parameters = array())
    {
        if (!is_string($instantiator)) {
            throw new \InvalidArgumentException('Instantiator must be a PHP callback, class name, or Distill instantiable (string->string)');
        }

        // interpolation of parameters into instantiator string
        if (strpos($instantiator, '{') !== false) {
            while (preg_match('#{([^}]+)}#', $instantiator, $subMatches)) {
                if (!isset($parameters[$subMatches[1]])) {
                    throw new \RuntimeException('Cannot substitute ' . $subMatches[1]);
                }
                if (!is_scalar($parameters[$subMatches[1]])) {
                    throw new \RuntimeException($subMatches[0] . ' replacement found but is not a scalar');
                }
                $instantiator = str_replace($subMatches[0], (string) $parameters[$subMatches[1]], $instantiator);
            }
        }

        // service, method
        list($s, $m) = (strpos($instantiator, '->') !== false) ? preg_split('#->#', $instantiator, 2) : [$instantiator, null];

        if ($s == null) {
            throw new \InvalidArgumentException('Provided instantiator does not look like a valid instantiator');
        }

        if (!class_exists($s, true)) {
            throw new \InvalidArgumentException('Class in instantiator cannot be located: ' . $s);
        }

        $a = $this->matchArguments(array($s, '__construct'), $parameters);

        switch (count($a)) {
            case 0: $o = new $s(); break;
            case 1: $o = new $s($a[0]); break;
            case 2: $o = new $s($a[0], $a[1]); break;
            case 3: $o = new $s($a[0], $a[1], $a[2]); break;
            case 4: $o = new $s($a[0], $a[1], $a[2], $a[3]); break;
            default:
                $r = new \ReflectionClass($s);
                $o = $r->newInstanceArgs($a);
        }

        return ($m) ? array($o, $m) : $o;
    }

    public function invoke($callable, $parameters = array(), $scope = null, &$invokedCallable = null)
    {
        if (is_string($callable) && strpos($callable, '->') !== false) {
            $callable = $this->instantiate($callable, $parameters);
        }

        $c = ($callable instanceof \Closure) ? $callable->bindTo($scope, get_class($scope)) : $callable;
        $a = $this->matchArguments($c, $parameters);

        if (!is_callable($c)) {
            throw new \RuntimeException('The constructed callable is actually not callable');
        }

        if (is_string($c) && strpos($c, '::') !== false) {
            $c = explode('::', $c);
        }

        $invokedCallable = $c;
        switch (count($a)) {
            case 0: return $c();
            case 1: return $c($a[0]);
            case 2: return $c($a[0], $a[1]);
            case 3: return $c($a[0], $a[1], $a[2]);
            case 4: return $c($a[0], $a[1], $a[2], $a[3]);
            default: return call_user_func_array($c, $a);
        }
    }

    public function validate(array $nameExpectedTypeMap)
    {
        foreach ($nameExpectedTypeMap as $name => $expectedType) {
            $service = $this->get($name);
            switch ($expectedType) {
                case 'is_callable':
                    if (!is_callable($service)) {
                        throw new \UnexpectedValueException($name . ' was found, but was not is_callable()');
                    }
                    break;
                default:
                    if (!$service instanceof $expectedType) {
                        throw new \UnexpectedValueException($name . ' was found, but was not of type ' . $expectedType);
                    }
            }
        }
        return true;
    }

    /**
     * @param $name
     * @return ServiceLocator
     * @throws \InvalidArgumentException
     */
    public function remove($name)
    {
        if (!isset($this->factories[$name])) {
            throw new \InvalidArgumentException($name . ' is not a registered service.');
        }
        if (isset($this->modifiables[$name]) && $this->modifiables[$name] === false) {
            throw new \InvalidArgumentException(
                'This service ' . $name . ' is marked as unmodifiable and therefore cannot be removed.'
            );
        }
        unset($this->factories[$name], $this->modifiables[$name]);
        return $this;
    }

    public function isServiceCallable($callable)
    {
        return (
            is_string($callable)
            && (isset($this->factories[$callable]) || isset($this->instances[$callable]) || strpos($callable, '->') !== false)
        );
    }

    public function matchArguments($callable, $parameters)
    {
        /** @var \ReflectionParameter[][] */
        static $funcRefs = array();
        if (!is_array($parameters) && !$parameters instanceof \ArrayAccess) {
            throw new \InvalidArgumentException('$parameters for ' . __CLASS__ . ' must be array or ArrayAccess');
        }

        // determine reference name
        if (is_string($callable)) {
            $refName = $callable;
            $refType = (strpos($callable, '::') !== false) ? 'ReflectionMethod' : 'ReflectionFunction';
        } elseif ($callable instanceof \Closure) {
            $refName = spl_object_hash($callable);
            $refType = 'ReflectionFunction';
        } elseif (is_array($callable)) {
            if (method_exists($callable[0], $callable[1])) {
                $refName = (is_object($callable[0]) ? get_class($callable[0]) : $callable[0]) . '->' . $callable[1];
                $refType = 'ReflectionClass';
            } elseif (method_exists($callable[0], '__call')) {
                return $parameters;
            } else {
                return array();
            }
        } elseif (is_object($callable) && is_callable($callable)) {
            $refName = get_class($callable);
            $refType = 'ReflectionClass';
        } else {
            throw new \RuntimeException('Unknown reflection method type');
        }

        if (!isset($funcRefs[$refName])) {
            $args = $callable;
            if (strpos($refName, '::') !== false) {
                $args = explode('::', $refName);
            } elseif (strpos($refName, '->')) {
                $args = explode('->', $refName);
            }
            $r = (is_array($args))
                ? ($refType == 'ReflectionClass')
                    ? (new $refType($args[0]))->getMethod($args[1])
                    : new $refType($args[0], $args[1])
                : new $refType($args);
            $funcRefs[$refName] = $r->getParameters();
        }

        $matchedArgs = array();

        foreach ($funcRefs[$refName] as $rp) {
            // check if it has a type hint, using its reflection class
            $typehintRef = $rp->getClass();
            if ($typehintRef) {
                $reqType = strtolower($typehintRef->getName());
            }

            if (isset($reqType) && isset($this->types[$reqType])) {
                $matchedArgs[] = $this->get($this->types[$reqType]);
                unset($reqType);
                continue;
            }

            // get param name
            $paramName = $rp->getName();

            if (isset($parameters[$paramName])) {
                // call-time arguments get priority
                $matchedArgs[] = $parameters[$paramName];
                continue;
            }
            if (isset($this->factories[$paramName]) || isset($this->instances[$paramName])) {
                $matchedArgs[] = $this[$paramName];
                continue;
            }
            if ($typehintRef) {
                foreach ($parameters as $parameter) {
                    if (is_object($parameter) && $typehintRef->isInstance($parameter)) {
                        $matchedArgs[] = $parameter;
                        continue 2;
                    }
                }
            }

            if ($rp->isOptional()) {
                // use default specified by method signature
                $matchedArgs[] = $rp->getDefaultValue();
                continue;
            }

            $subject = preg_replace('#\s+#', ' ', (string) $r);
            throw new \RuntimeException('Could not find a match for ' . $rp . ' of ' . $subject);
        }
        return $matchedArgs;
    }

    /**
     * @param mixed $name
     * @param mixed $service
     * @return ServiceLocator|void
     */
    public function offsetSet($name, $service)
    {
        return $this->set($name, $service);
    }

    /**
     * @param mixed $name
     * @return mixed
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * @param mixed $name
     * @return bool
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * @param mixed $name
     * @return ServiceLocator|void
     */
    public function offsetUnset($name)
    {
        return $this->remove($name);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->factories);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }
}
