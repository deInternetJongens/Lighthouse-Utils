<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\ArgumentValue;
use Nuwave\Lighthouse\Support\Contracts\ArgMiddleware;
use Nuwave\Lighthouse\Support\Traits\HandlesQueryFilter;

class GreaterThanEqualsDirective extends BaseDirective implements ArgMiddleware
{
    use HandlesQueryFilter;

    public function name()
    {
        return 'gte';
    }

    public function handleArgument(ArgumentValue $argument)
    {
        return $this->injectFilter(
            $argument,
            [
                'resolve' => function (Builder $builder, string $key, array $arguments) {
                    $value = $arguments[$key];

                    $field = \preg_replace('/_gte$/', '', $key);

                    return $builder->where($field, '>=', $value);
                },
            ]
        );
    }
}
