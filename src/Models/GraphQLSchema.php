<?php

namespace DeInternetJongens\LighthouseUtils\Models;

use Illuminate\Database\Eloquent\Model;

class GraphQLSchema extends Model
{
    protected $table = 'graphql_schema';

    protected $guarded = ['id'];

    public static function register($action, $model, $type, $permission = null)
    {
        return static::create([
            'name' => $action . $model,
            'type' => $type,
            'model' => $model,
            'permission' => $permission,
        ]);
    }
}