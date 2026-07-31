<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDecision extends Model
{
    protected $table = 'application_decisions';

    protected $fillable = [
        'application_id',
        'decision_no',
        'decision_date',
        'file_path',
        'notes',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }
}
