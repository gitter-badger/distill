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
    protected $data = [];

    public function __construct(array $data = array())
    {
        $this->merge($data);
    }

    public function merge($data)
    {
        $this->data = array_replace_recursive($this->data, $data);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset)
    {
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
}