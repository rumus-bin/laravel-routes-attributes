<?php

namespace RumusBin\AttributesRouter\RoteAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class WhereNumber extends Where
{
    public function __construct(string $param)
    {
        $this->param = $param;
        $this->constraint = '[0-9]+';
    }
}
