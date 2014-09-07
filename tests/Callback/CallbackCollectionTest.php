<?php
/**
 * Project distillphp.
 * User: ralphschindler
 * Date: 8/12/14
 * Time: 9:30 AM
 */

namespace Distill\Test\Callback;

use Distill\Callback\CallbackCollection;

class CallbackCollectionTest extends \PHPUnit_Framework_TestCase
{

    public function testGetContext()
    {
        $callbackCollection = new CallbackCollection();
        $this->assertInstanceOf('Distill\Callback\CallbackContext', $callbackCollection->getCallbackContext());
    }

    public function testSetContext()
    {
        $callbackCollection = new CallbackCollection();
        $this->assertSame($callbackCollection, $callbackCollection->setCallbackContext(new CallbackCollection()));
    }

    public function testRemove()
    {
        $c = function () {};

        $callbackCollection = new CallbackCollection();
        $callbackCollection->insert($c, 1);
        $this->assertCount(1, $callbackCollection);
        $callbackCollection->remove($c);
        $this->assertCount(0, $callbackCollection);
    }

}
