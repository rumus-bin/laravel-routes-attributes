<?php

namespace RumusBin\AttributesRouter\RoteAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Where implements WhereAttribute
{
    public function __construct(
        public string $param,
        public string $constraint
    ) {
    }
}
