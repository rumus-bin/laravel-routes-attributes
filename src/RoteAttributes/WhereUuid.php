<?php

namespace RumusBin\AttributesRouter\RoteAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class WhereUuid extends Where
{
    public function __construct(string $param)
    {
        $this->param = $param;
        $this->constraint = '[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}';
    }
}
