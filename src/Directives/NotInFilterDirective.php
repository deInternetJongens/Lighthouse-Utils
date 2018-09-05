<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;

class NotInFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, $value, Builder $builder): Builder
    {
        return $builder->whereNotIn($fieldName, $value);
    }

    public function name(): string
    {
        return 'not_in';
    }
}
