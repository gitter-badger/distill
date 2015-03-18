<?php

namespace Distill\Test\ServiceLocator\TestAsset;

class A
{
    public function doA()
    {
        return true;
    }

    public function __invoke()
    {
        return true;
    }
}
