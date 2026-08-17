<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    protected $table = 'educations';

    protected $fillable = [
        'application_id',
        'education_level_id',
        'country_id',
        'university_id',
        'faculty',
        'department',
        'section_name',
        'general_specialization',
        'exact_specialization',
        'registration_date',
        'graduation_date',
        'grant_date',
        'defense_date',
        'rank',
        'supervisor',
        'supervisor_name',
        'thesis_title',
        'envoy_decision',
        'envoy_date',
        'experience_from_year',
        'experience_to_year',
        'study_language',
        'duration_years',
        'notes',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function level()
    {
        return $this->belongsTo(LookupEducationLevel::class, 'education_level_id');
    }

    public function country()
    {
        return $this->belongsTo(LookupCountry::class, 'country_id');
    }

    public function university()
    {
        return $this->belongsTo(LookupUniversity::class, 'university_id');
    }

    public function attachments()
    {
        return $this->hasMany(EducationAttachment::class, 'education_id');
    }

    public function residences()
    {
        return $this->hasMany(EducationResidence::class, 'education_id');
    }

    public function getTypeOrFacultyAttribute()
    {
        // For High school: section_name holds 'علمي'/'أدبي'/'شرعي' etc.
        return $this->general_specialization 
            ?: ($this->faculty 
            ?: ($this->section_name 
            ?: ($this->attributes['type_or_faculty'] ?? '')));
    }

    public function getSpecializationOrDeptAttribute()
    {
        return $this->exact_specialization 
            ?: ($this->department 
            ?: ($this->section_name 
            ?: ($this->attributes['specialization_or_dept'] ?? '')));
    }

    public function getRankOrGradeAttribute()
    {
        return $this->rank 
            ?: ($this->attributes['rank_or_grade'] ?? '---');
    }

    public function getDecisionNoAttribute()
    {
        if (!empty($this->attributes['decision_no'])) {
            return $this->attributes['decision_no'];
        }
        if (!empty($this->notes)) {
            if (preg_match('/قرار[^:]*:\s*([^\|]+)/u', $this->notes, $m)) {
                return trim($m[1]);
            }
        }
        return 'لا يوجد';
    }
}
