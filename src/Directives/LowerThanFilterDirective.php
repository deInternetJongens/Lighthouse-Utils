<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;

class LowerThanFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, string $value, Builder $builder): Builder
    {
        return $builder->where($fieldName, '<', $value);
    }

    public function name(): string
    {
        return 'lt';
    }
}
