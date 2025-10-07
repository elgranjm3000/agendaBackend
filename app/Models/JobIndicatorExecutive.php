<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobIndicatorExecutive extends Model
{
    use HasFactory;

    protected $table = 'job_indicators_executive';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'period',
        'id_executive',
        'title',
        'amount',
        'maskAmount',
        'footer',
        'title_color',
        'y1',
        'x1',
        'y2',
        'x2',
    ];

    protected $casts = [
        'type' => 'integer',
        'amount' => 'float',
        'y1' => 'array',
        'x1' => 'array',
        'y2' => 'array',
        'x2' => 'array',
    ];

    public function executive(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_executive', 'id_user');
    }

    public function scopeByExecutive($query, $executiveId)
    {
        return $query->where('id_executive', $executiveId);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getChartDataAttribute(): array
    {
        return [
            'y1' => $this->y1,
            'x1' => $this->x1,
            'y2' => $this->y2,
            'x2' => $this->x2,
        ];
    }
}