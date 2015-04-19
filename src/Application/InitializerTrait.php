<?php

namespace Distill\Application;

trait InitializerTrait
{
    public function initialize($methodPrefix = 'initialize')
    {
        if (isset($this->isInitialized) && $this->isInitialized == true) {
            return false;
        }

        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if ($method !== $methodPrefix && $method !== 'initialize' && strpos($method, $methodPrefix) === 0) {
                $this->{$method}();
            }
        }

        parent::initialize();
        return true;
    }
}

