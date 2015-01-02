<?php

namespace Distill\Test\ServiceLocator;

use Distill\ServiceLocator\ServiceLocator;

class ServiceLocatorTest extends \PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $sl = new ServiceLocator();

        $class = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $sl['A'] = function () { return new TestAsset\A; };
        $a1 = $sl->get('A');
        $a2 = $sl->get('A');
        $this->isInstanceOf($class, $a1);
        $this->assertSame($a1, $a2);

        $sl->set('AnotherA', new TestAsset\A);
        $this->isInstanceOf($class, $sl->get('AnotherA'));
    }

    public function testGetWillResolveTypes()
    {
        $classA = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $classB = 'Distill\Test\ServiceLocator\TestAsset\B'; // TestAsset\B::class

        $sl = new ServiceLocator();
        $sl['A'] = new TestAsset\A;
        $this->assertInstanceOf($classB, $b = $sl->instantiate($classB));
        $this->assertInstanceOf($classA, $b->a);

        $sl = new ServiceLocator();
        $sl->set('A', function () { return new TestAsset\A; }, $classA);
        $this->assertInstanceOf($classB, $b = $sl->instantiate($classB));
        $this->assertInstanceOf($classA, $b->a);
    }

    public function testGetNew()
    {
        $sl = new ServiceLocator();

        $class = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $sl['A'] = function () { return new TestAsset\A; };
        $a1 = $sl->getNew('A');
        $a2 = $sl->getNew('A');
        $this->isInstanceOf($class, $a1);
        $this->assertNotSame($a1, $a2);
    }

    public function testInstantiate()
    {
        $sl = new ServiceLocator();

        $class = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $a = $sl->instantiate($class);
        $this->isInstanceOf($class, $a);
    }

    public function testInvoke()
    {
        $sl = new ServiceLocator();

        $class = 'Distill\Test\ServiceLocator\TestAsset\A'; // TestAsset\A::class
        $a = $sl->instantiate($class);
        $this->assertTrue($sl->invoke([$a, 'doA']));
    }
}
