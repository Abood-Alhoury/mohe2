<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquivalenceProfile extends Model
{
    protected $table = 'equivalence_profiles';

    protected $fillable = [
        'full_name',
        'national_id',
        'dob',
        'job_title',
        'nationality_id',
        'phone',
        'mobile',
        'email',
        'address',
        'gender',
        'is_syrian',
    ];

    public function nationality()
    {
        return $this->belongsTo(LookupCountry::class, 'nationality_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'candidate_id');
    }
}
