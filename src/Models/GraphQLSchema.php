<?php

namespace DeInternetJongens\LighthouseUtils\Models;

use Illuminate\Database\Eloquent\Model;

class GraphQLSchema extends Model
{
    protected $table = 'graphql_schema';

    protected $guarded = ['id'];

    public static function register($action, $model, $type, $permission = null)
    {
        if (config('lighthouse-utils.authorization')) {
            return static::create([
                'name' => $action,
                'type' => $type,
                'model' => $model,
                'permission' => $permission,
            ]);
        }

        return null;
    }
}
