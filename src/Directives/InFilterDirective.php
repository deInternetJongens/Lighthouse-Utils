<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;

class InFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, string $value, Builder $builder): Builder
    {
        return $builder->whereIn($fieldName, explode(',', $value));
    }

    public function name(): string
    {
        return 'in';
    }
}
