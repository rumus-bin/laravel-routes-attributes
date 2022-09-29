<?php

namespace RumusBin\AttributesRouter\RoteAttributes;

class Group implements RouteAttribute
{
    public function __construct(
        public ?string $prefix = null,
        public ?string $domain = null,
        public ?string $as = null,
        public ?array $where = []
    ) {
    }
}
