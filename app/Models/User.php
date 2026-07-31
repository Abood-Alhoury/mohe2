<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'university_id',
        'name',
        'email',
        'password',
        'is_active',
        'card_status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function university()
    {
        return $this->belongsTo(LookupUniversity::class, 'university_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class, 'user_id');
    }
}
