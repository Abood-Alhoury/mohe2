<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationMessage extends Model
{
    protected $table = 'application_messages';

    protected $fillable = [
        'application_id',
        'sender_id',
        'message',
        'is_read',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
