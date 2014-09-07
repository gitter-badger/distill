<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:29 AM
 */

namespace Distill\Test;

use Distill\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Distill\Configuration::merge
     * @testdox unit test: test merge()
     */
    public function testMerge()
    {
        $c1 = ['foo' => 1, 'bar' => 2];
        $c2 = ['bar' => 3];
        $config = new Configuration($c1);
        $config->merge($c2);
        $this->assertEquals(3, $config['bar']);
    }

    /**
     * @covers Distill\Configuration::offsetExists
     * @testdox unit test: test offsetExists()
     */
    public function testOffsetExists()
    {
        $c = ['foo' => 1, 'bar' => 2];
        $config = new Configuration($c);
        $this->assertTrue($config->offsetExists('bar'));
        $this->assertFalse($config->offsetExists('boom'));
    }

    /**
     * @covers Distill\Configuration::offsetGet
     * @testdox unit test: test offsetGet()
     */
    public function testOffsetGet()
    {
        $c = ['foo' => 1, 'bar' => 2];
        $config = new Configuration($c);
        $this->assertEquals(2, $config->offsetGet('bar'));
    }

    /**
     * @covers Distill\Configuration::offsetSet
     * @testdox unit test: test offsetSet()
     */
    public function testOffsetSet()
    {
        $config = new Configuration();
        $this->setExpectedException('Exception', 'must be merged');
        $config->offsetSet('bar', 2);
    }

    /**
     * @covers Distill\Configuration::offsetUnset
     * @testdox unit test: test offsetUnset()
     */
    public function testOffsetUnset()
    {
        $config = new Configuration();
        $this->setExpectedException('Exception', 'must be merged');
        $config->offsetUnset('bar');
    }

}
