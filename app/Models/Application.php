<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'candidate_id',
        'parent_application_id',
        'application_no',
        'request_type',
        'work_university_id',
        'work_faculty',
        'work_department',
        'new_uni_request_no',
        'new_uni_request_date',
        'is_first_time',
        'study_system',
        'has_previous_degree',
        'status',
        'user_id',
    ];

    public function parentApplication()
    {
        return $this->belongsTo(Application::class, 'parent_application_id');
    }

    public function transferChildren()
    {
        return $this->hasMany(Application::class, 'parent_application_id');
    }

    public function candidate()
    {
        return $this->belongsTo(EquivalenceProfile::class, 'candidate_id');
    }

    public function workUniversity()
    {
        return $this->belongsTo(LookupUniversity::class, 'work_university_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courses()
    {
        return $this->hasMany(ApplicationCourse::class, 'application_id');
    }

    public function educations()
    {
        return $this->hasMany(Education::class, 'application_id');
    }

    public function messages()
    {
        return $this->hasMany(ApplicationMessage::class, 'application_id')->orderBy('created_at', 'asc');
    }

    public function decisions()
    {
        return $this->hasMany(ApplicationDecision::class, 'application_id')->latest();
    }

    public function latestDecision()
    {
        return $this->hasOne(ApplicationDecision::class, 'application_id')->latestOfMany();
    }
}
