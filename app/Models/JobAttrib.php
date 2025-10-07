<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobAttrib extends Model
{
    use HasFactory;

    protected $table = 'job_attrib';

    public $timestamps = false;

    protected $fillable = [
        'id_type',
        'attrib1', 'attrib2', 'attrib3', 'attrib4', 'attrib5',
        'attrib6', 'attrib7', 'attrib8', 'attrib9', 'attrib10',
        'attrib11', 'attrib12', 'attrib13', 'attrib14', 'attrib15',
        'attrib16', 'attrib17', 'attrib18', 'attrib19', 'attrib20',
        'attrib21', 'attrib22',
    ];

    protected $casts = [
        'id_type' => 'integer',
    ];

    public function getAllAttributesArray(): array
    {
        $attributes = [];
        for ($i = 1; $i <= 22; $i++) {
            $key = "attrib{$i}";
            if (!empty($this->$key)) {
                $attributes[$key] = $this->$key;
            }
        }
        return $attributes;
    }

    public function scopeByType($query, $type)
    {
        return $query->where('id_type', $type);
    }
}