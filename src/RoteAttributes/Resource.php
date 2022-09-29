<?php

namespace RumusBin\AttributesRouter\RoteAttributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Resource implements RouteAttribute
{
    public function __construct(
        public string $resource,
        public bool $apiResource = false,
        public array | string | null $except = null,
        public array | string | null $only = null,
        public array | string | null $names = null,
    ) {
    }
}
