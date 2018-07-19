<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;

class StartsWithFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, string $value, Builder $builder): Builder
    {
        return $builder->where($fieldName, 'LIKE', "$value%");
    }

    public function name(): string
    {
        return 'starts_with';
    }
}
