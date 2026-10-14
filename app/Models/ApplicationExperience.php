<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ApplicationExperience extends Model
{
    protected $table = 'application_experiences';

    protected $fillable = [
        'application_id',
        'education_id',
        'institution_name',
        'role_or_subject',
        'start_date',
        'end_date',
        'duration_days',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_days' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function education()
    {
        return $this->belongsTo(Education::class, 'education_id');
    }

    /**
     * Compute human-friendly duration: X سنة و Y شهر و Z يوم
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return '—';
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        if ($end->lt($start)) {
            return '—';
        }

        $diff = $start->diff($end);
        $parts = [];
        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' . ($diff->y == 1 ? 'سنة' : ($diff->y == 2 ? 'سنتان' : 'سنوات'));
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . ($diff->m == 1 ? 'شهر' : ($diff->m == 2 ? 'شهران' : 'أشهر'));
        }
        if ($diff->d > 0 || empty($parts)) {
            $parts[] = $diff->d . ' ' . ($diff->d == 1 ? 'يوم' : ($diff->d == 2 ? 'يومان' : 'أيام'));
        }

        return implode(' و ', $parts);
    }
}
