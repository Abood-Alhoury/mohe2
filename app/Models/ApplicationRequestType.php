<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationRequestType extends Model
{
    protected $table = 'application_request_types';
    protected $fillable = ['name'];

    public function applications()
    {
        return $this->hasMany(Application::class, 'request_type_id');
    }
}
