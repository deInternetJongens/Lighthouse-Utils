<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;

class LowerThanFilterDirective extends BaseDirective
{
    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, $value, Builder $builder): Builder
    {
        return $builder->where($fieldName, '<', $value);
    }

    public function name(): string
    {
        return 'lt';
    }
}
