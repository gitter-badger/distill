<?php

namespace Distill\Test\ServiceLocator\TestAsset;

class B
{
    public $a;
    public function __construct(A $a)
    {
        $this->a = $a;
    }
}
