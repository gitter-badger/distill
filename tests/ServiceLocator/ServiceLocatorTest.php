<?php

namespace Distill\Test\ServiceLocator;

use Distill\ServiceLocator\ServiceLocator;

class ServiceLocatorTest extends \PHPUnit_Framework_TestCase
{

    public function testGet()
    {
        $sl = new ServiceLocator();

        $class = TestAsset\A::class;
        $sl['A'] = function () { return new TestAsset\A; };
        $a1 = $sl->get('A');
        $a2 = $sl->get('A');
        static::isInstanceOf($class, $a1);
        static::assertSame($a1, $a2);

        $sl->set('AnotherA', new TestAsset\A);
        static::isInstanceOf($class, $sl->get('AnotherA'));
    }

    public function testGetWillResolveTypes()
    {
        $classA = TestAsset\A::class;
        $classB = TestAsset\B::class;

        $sl = new ServiceLocator();
        $sl['A'] = new TestAsset\A;
        static::assertInstanceOf($classB, $b = $sl->instantiate($classB));
        static::assertInstanceOf($classA, $b->a);

        $sl = new ServiceLocator();
        $sl->set('A', function () { return new TestAsset\A; }, $classA);
        static::assertInstanceOf($classB, $b = $sl->instantiate($classB));
        static::assertInstanceOf($classA, $b->a);
    }

    public function testGetNew()
    {
        $sl = new ServiceLocator();

        $class = TestAsset\A::class;
        $sl['A'] = function () { return new TestAsset\A; };
        $a1 = $sl->getNew('A');
        $a2 = $sl->getNew('A');
        static::isInstanceOf($class, $a1);
        static::assertNotSame($a1, $a2);

        //$sl['A-alt'] = TestAsset\A::class;
        //static::isInstanceOf($sl->getNew('A-alt'));
    }

    public function testInstantiate()
    {
        $sl = new ServiceLocator();

        $class = TestAsset\A::class;
        $a = $sl->instantiate($class);
        static::isInstanceOf($class, $a);
    }

    public function testInvoke()
    {
        $sl = new ServiceLocator();

        $class = TestAsset\A::class;
        $a = $sl->instantiate($class);
        static::assertTrue($sl->invoke([$a, 'doA']));
        static::assertTrue($sl->invoke(TestAsset\A::class));
    }
}
