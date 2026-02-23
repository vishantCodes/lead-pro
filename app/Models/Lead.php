<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'email',
        'state',
        'source_type',
        'status',
        'assigned_to',
        'campaign_id',
        'converted_at',
        'created_by',
        'revenue',
    ];

    protected $casts = [
        'converted_at' => 'datetime',
        'revenue' => 'decimal:2',
        'source_type' => 'string',
        'status' => 'string',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function clientNotes()
    {
        return $this->hasMany(ClientNote::class);
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySourceType($query, $sourceType)
    {
        return $query->where('source_type', $sourceType);
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function isConverted(): bool
    {
        return $this->status === 'converted';
    }

    public function convert()
    {
        $this->update([
            'status' => 'converted',
            'converted_at' => now(),
        ]);
    }

    public function assignTo(User $user)
    {
        $this->update(['assigned_to' => $user->id]);
    }

    public function unassign()
    {
        $this->update(['assigned_to' => null]);
    }
}
