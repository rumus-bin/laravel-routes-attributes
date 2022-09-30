<?php

namespace RumusBin\AttributesRouter;

use ReflectionClass;
use RumusBin\AttributesRouter\RoteAttributes\Domain;
use RumusBin\AttributesRouter\RoteAttributes\DomainFromConfig;
use RumusBin\AttributesRouter\RoteAttributes\Group;
use RumusBin\AttributesRouter\RoteAttributes\Middleware;
use RumusBin\AttributesRouter\RoteAttributes\Prefix;
use RumusBin\AttributesRouter\RoteAttributes\Resource;
use RumusBin\AttributesRouter\RoteAttributes\RouteAttribute;
use RumusBin\AttributesRouter\RoteAttributes\ScopeBindings;
use RumusBin\AttributesRouter\RoteAttributes\Where;

class ClassRouteAttributes
{
    public function __construct(private readonly ReflectionClass $reflectionClass)
    {
    }

    public function prefix(): ?string
    {
        /** @var Prefix $attribute */
        if (! $attribute = $this->getAttribute(Prefix::class)) {
            return null;
        }

        return $attribute->prefix;
    }

    public function domain(): ?string
    {
        /** @var Domain $attribute */
        if (! $attribute = $this->reflectionClass->getAttributes(Domain::class)) {
            return null;
        }

        return $attribute->domain;
    }

    public function domainFromConfig(): ?string
    {
        /** @var DomainFromConfig $attribute */
        if (! $attribute = $this->reflectionClass->getAttributes(DomainFromConfig::class)) {
            return null;
        }

        return config($attribute->domain);
    }

    public function groups(): array
    {
        $groups = [];

        $attributes = $this->reflectionClass->getAttributes(Group::class. \ReflectionAttribute::IS_INSTANCEOF);
        if (count($attributes)) {
            foreach ($attributes as $attribute) {
                $attributeClass = $attribute->newInstance();
                $groups[] = [
                    'domain' => $attributeClass->domain,
                    'prefix' => $attributeClass->prefix,
                    'where' => $attributeClass->where,
                    'as' => $attributeClass->as
                ];
            }

            return $groups;
        }

        $groups[] = [
            'domain' => $this->domainFromConfig() ?? $this->domain(),
            'prefix' => $this->prefix()
        ];

        return $groups;
    }

    public function resource(): ?string
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->resource;
    }

    public function apiResource(): ?string
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->apiResource;
    }

    public function except(): string | array | null
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->except;
    }

    public function only(): string | array | null
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->only;
    }

    public function names(): string | array | null
    {
        /** @var Resource $attribute */
        if (! $attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->names;
    }

    public function middleware(): array
    {
        /** @var Middleware $attribute */
        if (! $attribute = $this->getAttribute(Middleware::class)) {
            return [];
        }

        return $attribute->middleware;
    }

    public function scopeBindings(): bool
    {
        /** @var ScopeBindings $attribute */
        if (! $attribute = $this->getAttribute(ScopeBindings::class)) {
            return false;
        }

        return $attribute->scopeBindings;
    }

    public function wheres(): array
    {
        $wheres = [];

        $attributes = $this->reflectionClass
            ->getAttributes(Where::class, \ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $attributeClass = $attribute->newInstance();
            $wheres[$attributeClass->param] = $attributeClass->constrait;
        }

        return $wheres;
    }

    protected function getAttribute(string $class): ?RouteAttribute
    {
        $attributes = $this->reflectionClass->getAttributes($class, \ReflectionAttribute::IS_INSTANCEOF);

        if (! count($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}
