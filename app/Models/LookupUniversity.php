<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookupUniversity extends Model
{
    protected $table = 'lookup_universities';
    protected $fillable = ['country_id', 'name'];

    public function country()
    {
        return $this->belongsTo(LookupCountry::class, 'country_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'work_university_id');
    }
}
