<?php

namespace DeInternetJongens\LighthouseUtils\Directives;

use Illuminate\Database\Eloquent\Builder;

class FulltextFilterDirective extends BaseDirective
{
    public function name(): string
    {
        return 'fulltext';
    }

    public function handle(string $fieldName, string $value, Builder $builder): Builder
    {
       return $builder->getModel()->fullTextSearch($builder, $value);
    }
}
