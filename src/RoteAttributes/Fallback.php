<?php

namespace RumusBin\AttributesRouter\RoteAttributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Fallback
{
    public function __construct()
    {
    }
}
