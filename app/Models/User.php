<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role_id',
        'manager_id',
        'commission_rate',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function createdLeads()
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    public function campaignTasks()
    {
        return $this->hasMany(CampaignTask::class, 'assigned_to');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function approvedCommissions()
    {
        return $this->hasMany(Commission::class, 'approved_by');
    }

    public function clientNotes()
    {
        return $this->hasMany(ClientNote::class, 'created_by');
    }

    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class, 'sent_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->name === 'super_admin';
    }

    public function isAgencyAdmin(): bool
    {
        return $this->role && $this->role->name === 'agency_admin';
    }

    public function isManager(): bool
    {
        return $this->role && $this->role->name === 'manager';
    }

    public function isSalesExecutive(): bool
    {
        return $this->role && $this->role->name === 'sales_executive';
    }

    public function hasPermission($permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->role && $this->role->hasPermission($permission);
    }

    public function canManageTeam(): bool
    {
        return $this->isSuperAdmin() || $this->isAgencyAdmin() || $this->isManager();
    }

    public function getTeamMembers()
    {
        if ($this->isSuperAdmin() || $this->isAgencyAdmin()) {
            return User::where('tenant_id', $this->tenant_id)->get();
        }

        if ($this->isManager()) {
            return $this->subordinates;
        }

        return collect([$this]);
    }
}
