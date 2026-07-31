<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LookupCountry extends Model
{
    protected $table = 'lookup_countries';
    protected $fillable = ['name'];
}
