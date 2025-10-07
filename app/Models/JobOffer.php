<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOffer extends Model
{
    use HasFactory;

    protected $table = 'job_offers';
    protected $primaryKey = 'id_offers';

    protected $fillable = [
        'descrip',
        'date_begin',
        'date_end',
        'id_user',
        'is_life',
        'is_delete',
    ];

    protected $casts = [
        'date_begin' => 'date',
        'date_end' => 'date',
        'stamp' => 'datetime',
        'is_life' => 'boolean',
        'is_delete' => 'boolean',
    ];

    public function dayExecutives(): HasMany
    {
        return $this->hasMany(JobDayExecutive::class, 'id_offers', 'id_offers');
    }

    public function dayContacts(): HasMany
    {
        return $this->hasMany(JobDayContact::class, 'id_offers', 'id_offers');
    }

    public function scopeActive($query)
    {
        return $query->where('is_life', true)->where('is_delete', false);
    }

    public function scopeCurrent($query)
    {
        $today = now()->toDateString();
        return $query->where('date_begin', '<=', $today)
                     ->where('date_end', '>=', $today);
    }
}