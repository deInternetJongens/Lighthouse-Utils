<?php

namespace App\DijLightHouse\Directives;

use Illuminate\Database\Eloquent\Builder;
use Nuwave\Lighthouse\Support\Traits\HandlesGlobalId;

class EqGlobalIdFilterDirective extends BaseDirective
{
    use HandlesGlobalId;

    /**
     * @inheritdoc
     */
    public function handle(string $fieldName, $value, Builder $builder): Builder
    {
        $globalIdParts = $this->decodeGlobalId($value);

        if(count($globalIdParts) === 2) {
            $value = $globalIdParts[1];
        }

        return $builder->where($fieldName, $value);
    }

    public function name(): string
    {
        return 'eq';
    }
}
