<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationCourse extends Model
{
    protected $table = 'application_courses';

    protected $fillable = [
        'application_id',
        'faculty',
        'department',
        'course_name',
        'course_status',
        'status_date',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
