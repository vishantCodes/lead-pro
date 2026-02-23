<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campaign extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'budget',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => 'string',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function tasks()
    {
        return $this->hasMany(CampaignTask::class);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function activate()
    {
        $this->update(['status' => 'active']);
    }

    public function complete()
    {
        $this->update(['status' => 'completed']);
    }

    public function pause()
    {
        $this->update(['status' => 'paused']);
    }

    public function deactivate()
    {
        $this->update(['status' => 'paused']);
    }

    public function getLeadsCount(): int
    {
        return $this->leads()->count();
    }

    public function getConvertedLeadsCount(): int
    {
        return $this->leads()->converted()->count();
    }

    public function getConversionRate(): float
    {
        $totalLeads = $this->getLeadsCount();
        
        if ($totalLeads === 0) {
            return 0;
        }

        return ($this->getConvertedLeadsCount() / $totalLeads) * 100;
    }

    public function getTotalRevenue(): float
    {
        return $this->leads()->converted()->sum('revenue') ?? 0;
    }

    public function getCompletedTasksCount(): int
    {
        return $this->tasks()->where('status', 'completed')->count();
    }

    public function getTasksCount(): int
    {
        return $this->tasks()->count();
    }
}
