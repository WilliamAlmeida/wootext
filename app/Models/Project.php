<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'account_id',
        'name',
        'description',
        'status',
        'deadline',
        'color',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
        ];
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ProjectConversation::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function discussions(): HasMany
    {
        return $this->hasMany(ProjectDiscussion::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }
}
