<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationResidence extends Model
{
    protected $table = 'education_residences';

    protected $fillable = [
        'education_id',
        'page_number',
        'exit_airport',
        'exit_date',
        'entry_airport',
        'entry_date',
        'stamp_details',
    ];

    public function education()
    {
        return $this->belongsTo(Education::class, 'education_id');
    }
}
