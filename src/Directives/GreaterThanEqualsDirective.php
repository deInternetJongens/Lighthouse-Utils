<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;

class GreaterThanEqualsDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, $value, Builder $builder): Builder
    {
        return $builder->where($fieldName, '>=', $value);
    }

    public function name(): string
    {
        return 'gte';
    }
}
