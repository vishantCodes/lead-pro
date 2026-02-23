<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadActivity extends Model
{
    protected $fillable = [
        'tenant_id',
        'lead_id',
        'user_id',
        'type',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Convenience factory methods for common activity types
    public static function log(Lead $lead, string $type, string $description, array $meta = []): self
    {
        return self::create([
            'tenant_id'   => $lead->tenant_id,
            'lead_id'     => $lead->id,
            'user_id'     => auth()->id(),
            'type'        => $type,
            'description' => $description,
            'meta'        => $meta ?: null,
        ]);
    }
}
