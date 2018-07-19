<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective as LighthouseBaseDirective;
use Nuwave\Lighthouse\Schema\Values\ArgumentValue;
use Nuwave\Lighthouse\Support\Contracts\ArgMiddleware;
use Nuwave\Lighthouse\Support\Traits\HandlesGlobalId;
use Nuwave\Lighthouse\Support\Traits\HandlesQueryFilter;

abstract class BaseDirective extends LighthouseBaseDirective implements ArgMiddleware
{
    use HandlesQueryFilter, HandlesGlobalId;

    public function handleArgument(ArgumentValue $argument): ArgumentValue
    {
        return $this->injectFilter(
            $argument,
            [
                'resolve' => function (Builder $builder, string $key, array $arguments): Builder {
                    $value = $arguments[$key];

                    $field = \preg_replace(sprintf('/%s$/', $this->getSuffix()), '', $key);

                    $globalIdParts = $this->decodeGlobalId($value);

                    if(count($globalIdParts) === 2) {
                        $value = $globalIdParts[1];
                    }

                    return $this->handle($field, $value, $builder);
                },
            ]
        );
    }

    /**
     * Get the suffix for this query, e.g. : foo_contains, _contains is the suffix here.
     * @return string
     */
    protected function getSuffix(): string {
        return sprintf('_%s', $this->name());
    }

    /**
     * Add query statement to the eloquent builder.
     * @param string $fieldName
     * @param string $value
     * @param Builder $builder
     * @return Builder
     */
    abstract public function handle(string $fieldName, string $value, Builder $builder): Builder;
}
