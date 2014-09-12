<?php
/**
 * Distill Framework
 * @link http://github.com/pframework
 * @license UNLICENSE http://unlicense.org/UNLICENSE
 * @copyright Public Domain
 * @author Ralph Schindler <ralph@ralphschindler.com>
 */

namespace Distill;

class Configuration implements \ArrayAccess
{
    protected $environment = null;
    protected $applicationPath = null;

    protected $data = [];

    public function __construct(array $data = array(), $environment = null, $applicationPath = null)
    {
        $this->merge($data);
        $this->environment = $environment;
        $this->applicationPath = $applicationPath;
    }

    public function merge($data)
    {
        if ($data instanceof \Iterator) {
            $data = iterator_to_array($data);
        }
        $this->data = array_replace_recursive($this->data, $data);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
        if (in_array(strtolower($offset), ['env', 'environment'])) {
            return $this->environment;
        }
        if (in_array(strtolower($offset), ['application_path', 'app_path', 'applicationpath', 'apppath', 'path'])) {
            return $this->applicationPath;
        }
        return (isset($this->data[$offset])) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception('Configuration must be merged in');
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('Configuration changes must be merged in.');
    }

    public function __get($name)
    {
        return $this->offsetGet($name);
    }
}