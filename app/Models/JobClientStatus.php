<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobClientStatus extends Model
{
    use HasFactory;

    protected $table = 'job_client_status';
    protected $primaryKey = 'id_status';

    public $timestamps = false;

    protected $fillable = [
        'descrip',
        'is_life',
    ];

    protected $casts = [
        'is_life' => 'boolean',
    ];

    public function executives(): HasMany
    {
        return $this->hasMany(JobDayExecutive::class, 'id_status', 'id_status');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(JobDayContact::class, 'id_status', 'id_status');
    }

    public function contactStatuses(): HasMany
    {
        return $this->hasMany(JobClientStatusContact::class, 'id_status', 'id_status');
    }

    public function scopeActive($query)
    {
        return $query->where('is_life', true);
    }
}