<?php

namespace App\Tmp\Tmp34;

use App\Tmp\Tmp34\Interfaces\ICanFly;
use App\Tmp\Tmp34\TmpTmp\A;
use App\Tmp\Tmp34\TmpTmp\B;

class C
{
    public function __construct(
        protected ICanFly $canFly,
        protected A $a,
        protected B $b,
        protected array $c,
    )
    {
    }
}