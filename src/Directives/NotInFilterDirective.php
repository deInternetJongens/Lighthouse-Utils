<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;

class NotInFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, string $value, Builder $builder): Builder
    {
        return $builder->whereNotIn($fieldName, explode(',', $value));
    }

    public function name(): string
    {
        return 'not_in';
    }
}
