<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobDayContact extends Model
{
    use HasFactory;

    protected $table = 'job_day_contact';

    protected $fillable = [
        'id_offers',
        'id_phone',
        'id_client',
        'id_executive',
        'id_status',
        'id_contact',
        'scheduled_date',
    ];

    protected $casts = [
        'stamp' => 'datetime',
        'scheduled_date' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class, 'id_offers', 'id_offers');
    }

    public function phone(): BelongsTo
    {
        return $this->belongsTo(JobPhone::class, 'id_phone', 'id_phone');
    }

    public function executive(): BelongsTo
    {
        return $this->belongsTo(JobDayExecutive::class, 'id_client', 'id_client');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_executive', 'id_user');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(JobClientStatus::class, 'id_status', 'id_status');
    }

    public function contactStatus(): BelongsTo
    {
        return $this->belongsTo(JobClientStatusContact::class, 'id_contact', 'id_contact');
    }

    public function scopeByExecutive($query, $executiveId)
    {
        return $query->where('id_executive', $executiveId);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_date');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', now()->toDateString());
    }
}