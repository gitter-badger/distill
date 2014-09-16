<?php


namespace Distill\Callback;

class CallbackContext implements \ArrayAccess, \IteratorAggregate
{
    protected $parameters = array();
    protected $returns = array();

    public function pushReturn($return)
    {
        array_push($this->returns, $return);
        return $this;
    }

    public function getFirstReturn()
    {
        return reset($this->returns);
    }

    public function getLastReturn()
    {
        return end($this->returns);
    }

    public function getReturns()
    {
        return $this->returns;
    }

    public function setParameters(array $parameters, $append = false)
    {
        $this->parameters = ($append) ? array_merge($this->parameters, $parameters) : $parameters;
        return $this;
    }

    public function offsetGet($name)
    {
        if ($name == 'callbackContext') {
            return $this;
        }
        return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
    }

    public function offsetSet($name, $value)
    {
        if ($name == 'callbackContext') {
            throw new \InvalidArgumentException('callbackContext cannot be used as a parameter in this container');
        }
        $this->parameters[$name] = $value;
        return $this;
    }

    public function offsetUnset($name)
    {
        if ($name == 'callbackContext') {
            throw new \InvalidArgumentException('callbackContext cannot be unset');
        }
        unset($this->parameters[$name]);
    }

    public function offsetExists($name)
    {
        if ($name == 'callbackContext') {
            return true;
        }
        return array_key_exists($name, $this->parameters);
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }

    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }

    public function __isset($name)
    {
        return $this->offsetExists($name);
    }

    public function __unset($name)
    {
        $this->offsetUnset($name);
    }

    public function getIterator()
    {
        return new \ArrayIterator(array_merge($this->parameters, ['callbackContext' => $this]));
    }

}

 