<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective as LighthouseBaseDirective;
use Nuwave\Lighthouse\Schema\Values\ArgumentValue;
use Nuwave\Lighthouse\Support\Contracts\ArgMiddleware;
use Nuwave\Lighthouse\Support\Traits\HandlesQueryFilter;

abstract class BaseDirective extends LighthouseBaseDirective implements ArgMiddleware
{
    use HandlesQueryFilter;

    //TODO: Fix compatibility with Lighthouse 2.2
    public function handleArgument(ArgumentValue $argument, Closure $next): ArgumentValue
    {
        return $this->injectFilter(
            $argument,
            [
                'resolve' => function (Builder $builder, string $key, array $arguments): Builder {
                    $value = $arguments[$key];

                    $field = \preg_replace(sprintf('/%s$/', $this->getSuffix()), '', $key);

                    return $this->handle($field, $value, $builder);
                },
            ]
        );
    }

    /**
     * Get the suffix for this query, e.g. : foo_contains, _contains is the suffix here.
     * @return string
     */
    protected function getSuffix(): string
    {
        return sprintf('_%s', $this->name());
    }

    /**
     * Add query statement to the eloquent builder.
     *
     * @param string $fieldName
     * @param mixed $value The value can be a string, number, bool, etc… This mixed is on purpose!
     * @param Builder $builder
     * @return Builder
     */
    abstract public function handle(string $fieldName, $value, Builder $builder): Builder;
}
