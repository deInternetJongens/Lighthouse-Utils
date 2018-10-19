<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Schema\Values\ArgumentValue;
use Nuwave\Lighthouse\Support\Contracts\ArgMiddleware;
use Nuwave\Lighthouse\Support\Traits\HandlesQueryFilter;

class QueryableDirective extends \Nuwave\Lighthouse\Schema\Directives\BaseDirective implements ArgMiddleware
{
    use HandlesQueryFilter;

    /**
     * Apply transformations on the ArgumentValue.
     *
     * @param ArgumentValue $argument
     * @param \Closure $next
     *
     * @return ArgumentValue
     */
    public function handleArgument(ArgumentValue $argument, \Closure $next)
    {
        $argument = $this->injectFilter(
            $argument,
            function (Builder $builder, string $key, array $arguments): Builder {

                return $this->handle($key, $arguments, $builder);
            }
        );

        return $next($argument);
    }
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, array $arguments, Builder $builder): Builder
    {

        return $builder->where($fieldName, $arguments['operator'], $arguments['value']);
    }

    public function name(): string
    {
        return 'queryable';
    }

}
