<?php

namespace Distill\Test\Callback;

use Distill\Callback\CallbackContext;

class CallbackContextTest extends \PHPUnit_Framework_TestCase
{
    /** @var CallbackContext */
    protected $callbackContext;

    public function setup()
    {
        $this->callbackContext = new CallbackContext();
    }

    /**
     * @covers Distill\Callback\CallbackContext::pushReturn
     * @testdox unit test: test pushReturn()
     */
    public function testPushReturn()
    {
        $this->assertSame($this->callbackContext, $this->callbackContext->pushReturn(5));
    }

    /**
     * @covers Distill\Callback\CallbackContext::getFirstReturn
     * @testdox unit test: test getReturn()
     */
    public function testGetReturn()
    {
        $this->callbackContext->pushReturn(5);
        $this->assertEquals(5, $this->callbackContext->getFirstReturn());
    }

    /**
     * @covers Distill\Callback\CallbackContext::getReturns
     * @testdox unit test: test getReturns()
     */
    public function testGetReturns()
    {
        $this->callbackContext->pushReturn(5);
        $this->callbackContext->pushReturn(10);
        $this->assertEquals([5, 10], $this->callbackContext->getReturns());
    }

    /**
     * @covers Distill\Callback\CallbackContext::setParameters
     * @testdox unit test: test setParameters()
     */
    public function testSetParameters()
    {
        $return = $this->callbackContext->setParameters([
            'foo' => 'bar'
        ]);
        $this->assertSame($this->callbackContext, $return);
    }

    /**
     * @covers Distill\Callback\CallbackContext::offsetGet
     * @testdox unit test: test offsetGet()
     */
    public function testOffsetGet()
    {
        $this->callbackContext->offsetSet('foo', 'bar');
        $this->assertEquals('bar', $this->callbackContext->offsetGet('foo'));
    }

    /**
     * @covers Distill\Callback\CallbackContext::offsetSet
     * @testdox unit test: test offsetSet()
     */
    public function testOffsetSet()
    {
        $return = $this->callbackContext->offsetSet('foo', 'bar');
        $this->assertSame($this->callbackContext, $return);
    }

    /**
     * @covers Distill\Callback\CallbackContext::offsetUnset
     * @testdox unit test: test offsetUnset()
     */
    public function testOffsetUnset()
    {
        $this->callbackContext['foo'] = 'bar';
        $this->callbackContext->offsetUnset('foo');
        $this->assertFalse($this->callbackContext->offsetExists('foo'));
    }

    /**
     * @covers Distill\Callback\CallbackContext::offsetExists
     * @testdox unit test: test offsetExists()
     */
    public function testOffsetExists()
    {
        $this->callbackContext['foo'] = 'bar';
        $this->assertTrue($this->callbackContext->offsetExists('foo'));
    }

    /**
     * @covers Distill\Callback\CallbackContext::__get
     * @testdox unit test: test __get()
     */
    public function test__get()
    {
        $this->callbackContext['foo'] = 'bar';
        $this->assertEquals('bar', $this->callbackContext->foo);
    }

    /**
     * @covers Distill\Callback\CallbackContext::__set
     * @testdox unit test: test __set()
     */
    public function test__set()
    {
        $this->callbackContext->foo = 'bar';
        $this->assertEquals('bar', $this->callbackContext->offsetGet('foo'));
    }

    /**
     * @covers Distill\Callback\CallbackContext::__isset
     * @testdox unit test: test __isset()
     */
    public function test__isset()
    {
        $this->callbackContext->foo = 'bar';
        $this->assertTrue(isset($this->callbackContext->foo));
        $this->callbackContext->offsetUnset('foo');
        $this->assertFalse(isset($this->callbackContext->foo));
    }

    /**
     * @covers Distill\Callback\CallbackContext::__unset
     * @testdox unit test: test __unset()
     */
    public function test__unset()
    {
        $this->callbackContext->foo = 'bar';
        $this->assertTrue(isset($this->callbackContext->foo));
        $this->callbackContext->offsetUnset('foo');
        $this->assertFalse(isset($this->callbackContext->foo));
    }

    /**
     * @covers Distill\Callback\CallbackContext::getIterator
     * @testdox unit test: test getIterator()
     */
    public function testGetIterator()
    {
        $this->callbackContext->foo = 'bar';
        $iterator = $this->callbackContext->getIterator();
        $this->assertInstanceOf('ArrayIterator', $iterator);
    }


}
 