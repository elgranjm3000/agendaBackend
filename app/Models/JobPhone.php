<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPhone extends Model
{
    use HasFactory;

    protected $table = 'job_phone';
    protected $primaryKey = 'id_phone';

    public $timestamps = false;

    protected $fillable = [
        'id_client',
        'attrib1',
        'attrib2',
        'attrib3',
        'attrib4',
        'attrib5',
        'phone',
        'update_date',
    ];

    protected $casts = [
        'update_date' => 'date',
        'phone' => 'integer',
    ];

    public function contacts(): HasMany
    {
        return $this->hasMany(JobDayContact::class, 'id_phone', 'id_phone');
    }

    public function getFormattedPhoneAttribute(): string
    {
        $phone = (string) $this->phone;
        if (strlen($phone) === 9) {
            return '+56 ' . substr($phone, 0, 1) . ' ' . substr($phone, 1, 4) . ' ' . substr($phone, 5);
        }
        return $phone;
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('id_client', $clientId);
    }
}