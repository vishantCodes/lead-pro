<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Webhook extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'source_name',
        'endpoint_key',
        'endpoint_url',
        'is_active',
        'headers',
        'secret',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'headers' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeBySource($query, $source)
    {
        return $query->where('source_name', $source);
    }

    public function scopeByKey($query, $key)
    {
        return $query->where('endpoint_key', $key);
    }

    public function activate()
    {
        $this->update(['is_active' => true]);
    }

    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    public function toggleStatus()
    {
        $this->update(['is_active' => !$this->is_active]);
    }

    public function isValidSecret(string $providedSecret): bool
    {
        return hash_equals($this->secret, $providedSecret);
    }
}
