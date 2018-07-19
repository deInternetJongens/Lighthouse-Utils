<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;

class NotEndsWithFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, string $value, Builder $builder): Builder
    {
        return $builder->where($fieldName, 'NOT LIKE', "%$value");
    }

    public function name(): string
    {
        return 'not_ends_with';
    }
}
