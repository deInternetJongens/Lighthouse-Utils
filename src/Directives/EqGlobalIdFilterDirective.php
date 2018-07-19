<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\ArgumentValue;
use Nuwave\Lighthouse\Support\Contracts\ArgMiddleware;
use Nuwave\Lighthouse\Support\Traits\HandlesGlobalId;
use Nuwave\Lighthouse\Support\Traits\HandlesQueryFilter;

class EqGlobalIdFilterDirective extends BaseDirective implements ArgMiddleware
{
    use HandlesQueryFilter, HandlesGlobalId;

    public function name(): string
    {
        return 'eq';
    }

    public function handleArgument(ArgumentValue $argument): ArgumentValue
    {
        return $this->injectFilter(
            $argument,
            [
                'resolve' => function (Builder $builder, string $key, array $arguments): Builder {
                    $value = $arguments[$key];

                    $field = $key;

                    $globalIdParts = $this->decodeGlobalId($value);

                    if(count($globalIdParts) === 2) {
                        $value = $globalIdParts[1];
                    }

                    return $builder->where($field, $value);
                },
            ]
        );
    }
}
