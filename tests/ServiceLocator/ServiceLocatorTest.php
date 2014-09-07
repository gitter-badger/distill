<?php

namespace Distill\Test\ServiceLocator;

use Distill\ServiceLocator\ServiceLocator;

class ServiceLocatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceLocator */
    protected $serviceLocator;

    public function setup()
    {
        $this->serviceLocator = new ServiceLocator();
    }

    public function testGet()
    {
        $class = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $this->serviceLocator['A'] = function () { return new TestAsset\A; };
        $a1 = $this->serviceLocator->get('A');
        $a2 = $this->serviceLocator->get('A');
        $this->isInstanceOf($class, $a1);
        $this->assertSame($a1, $a2);

        $this->serviceLocator->set('AnotherA', new TestAsset\A);
        $this->isInstanceOf($class, $this->serviceLocator->get('AnotherA'));
    }

    public function testGetNew()
    {
        $class = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $this->serviceLocator['A'] = function () { return new TestAsset\A; };
        $a1 = $this->serviceLocator->getNew('A');
        $a2 = $this->serviceLocator->getNew('A');
        $this->isInstanceOf($class, $a1);
        $this->assertNotSame($a1, $a2);
    }

    public function testInstantiate()
    {
        $class = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $a = $this->serviceLocator->instantiate($class);
        $this->isInstanceOf($class, $a);
    }

    public function testInvoke()
    {
        $class = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $a = $this->serviceLocator->instantiate($class);
        $this->assertTrue($this->serviceLocator->invoke([$a, 'doA']));
    }
}
