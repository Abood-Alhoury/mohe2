<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookupEducationLevel extends Model
{
    protected $table = 'lookup_education_levels';
    protected $fillable = ['name'];

    public function educations()
    {
        return $this->hasMany(Education::class, 'education_level_id');
    }
}
