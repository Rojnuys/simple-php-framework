<?php

namespace App\Tmp\Tmp34\TmpTmp;

use App\Tmp\Tmp34\Interfaces\ICanFly;

class B extends A implements ICanFly
{
    public function __construct(protected A $a)
    {
        parent::__construct([]);
    }

    public function fly(): void
    {
        // TODO: Implement fly() method.
    }
}