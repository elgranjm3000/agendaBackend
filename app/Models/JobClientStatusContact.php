<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobClientStatusContact extends Model
{
    use HasFactory;

    protected $table = 'job_client_status_contact';
    protected $primaryKey = 'id_contact';

    public $timestamps = false;

    protected $fillable = [
        'descrip',
        'id_status',
        'is_life',
        'is_scheduled',
    ];

    protected $casts = [
        'is_life' => 'boolean',
        'is_scheduled' => 'boolean',
    ];

    public function status(): BelongsTo
    {
        return $this->belongsTo(JobClientStatus::class, 'id_status', 'id_status');
    }

    public function executives(): HasMany
    {
        return $this->hasMany(JobDayExecutive::class, 'id_contact', 'id_contact');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(JobDayContact::class, 'id_contact', 'id_contact');
    }

    public function scopeActive($query)
    {
        return $query->where('is_life', true);
    }

    public function scopeScheduled($query)
    {
        return $query->where('is_scheduled', true);
    }
}