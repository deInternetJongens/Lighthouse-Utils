<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\ArgumentValue;
use Nuwave\Lighthouse\Support\Contracts\ArgMiddleware;
use Nuwave\Lighthouse\Support\Traits\HandlesQueryFilter;

class NotInFilterDirective extends BaseDirective implements ArgMiddleware
{
    use HandlesQueryFilter;

    public function name()
    {
        return 'not_in';
    }

    public function handleArgument(ArgumentValue $argument)
    {
        return $this->injectFilter(
            $argument,
            [
                'resolve' => function (Builder $builder, string $key, array $arguments) {
                    $value = $arguments[$key];

                    $values = explode(',', $value);

                    $field = \preg_replace('/_not_in/', '', $key);

                    return $builder->whereNotIn($field, $values);
                },
            ]
        );
    }
}
