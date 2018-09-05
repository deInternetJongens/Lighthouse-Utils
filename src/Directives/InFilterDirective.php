<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;

class InFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, $value, Builder $builder): Builder
    {
        return $builder->whereIn($fieldName, $value);
    }

    public function name(): string
    {
        return 'in';
    }
}
