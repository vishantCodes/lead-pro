<?php

namespace App\Models\Traits;

use App\Models\Scopes\BelongsToTenantScope;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::addGlobalScope(new BelongsToTenantScope);
        
        static::creating(function (Model $model) {
            if (auth()->check() && !auth()->user()->isSuperAdmin()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
