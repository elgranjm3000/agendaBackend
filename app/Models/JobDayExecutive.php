<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobDayExecutive extends Model
{
    use HasFactory;

    protected $table = 'job_day_executive';

    protected $fillable = [
        'id_offers',
        'id_client',
        'dv_client',
        'id_executive',
        'dv_executive',
        'name',
        'last_name1',
        'last_name2',
        'id_office',
        'attrib1', 'attrib2', 'attrib3', 'attrib4', 'attrib5',
        'attrib6', 'attrib7', 'attrib8', 'attrib9', 'attrib10',
        'attrib11', 'attrib12', 'attrib13', 'attrib14', 'attrib15',
        'attrib16', 'attrib17', 'attrib18', 'attrib19', 'attrib20',
        'attrib21', 'attrib22',
        'id_status',
        'id_contact',
        'scheduled_date',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'stamp' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class, 'id_offers', 'id_offers');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(JobClientStatus::class, 'id_status', 'id_status');
    }

    public function contactStatus(): BelongsTo
    {
        return $this->belongsTo(JobClientStatusContact::class, 'id_contact', 'id_contact');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(JobDayContact::class, 'id_client', 'id_client')
        ->where('id_status', 2)
        ->whereColumn('job_day_contact.id_offers', 'id_offers');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->name} {$this->last_name1} {$this->last_name2}");
    }

    public function scopeByOffice($query, $officeId)
    {
        return $query->where('id_office', $officeId);
    }

    public function scopeByStatus($query, $statusId)
    {
        return $query->where('id_status', $statusId);
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_date');
    }
}