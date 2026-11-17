<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Builders\ApplicationBuilder;

class Application extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'candidate_id',
        'parent_application_id',
        'application_no',
        'request_type',
        'work_university_id',
        'work_faculty',
        'work_department',
        'new_uni_request_no',
        'new_uni_request_date',
        'is_first_time',
        'study_system',
        'has_previous_degree',
        'is_residence_exempt',
        'status',
        'interview_date',
        'interview_time',
        'interview_notes',
        'eligibility_decision_no',
        'eligibility_decision_date',
        'user_id',
        'committee_track',
        'experience_approved',
        'scientific_production_approved',
        'scientific_production_date',
        'scientific_production_decision_no',
        'scientific_production_notes',
        'rejection_reason',
    ];

    protected $casts = [
        'is_residence_exempt' => 'boolean',
    ];

    public function newEloquentBuilder($query)
    {
        return new ApplicationBuilder($query);
    }

    public function getStatusAttribute($value)
    {
        if (is_numeric($value)) {
            return ApplicationStatus::find($value)?->name ?? $value;
        }
        return $value;
    }

    public function setStatusAttribute($value)
    {
        if (is_string($value) && !is_numeric($value)) {
            if ($value === 'إنتاج علمي' || $value === 'بانتظار لجنة الإنتاج العلمي') {
                $id = ApplicationStatus::where('name', 'بانتظار لجنة إنتاج علمي')->value('id') ?? 5;
            } else {
                $id = ApplicationStatus::where('name', $value)->value('id');
            }
            $this->attributes['status'] = $id ?? 2;
        } else {
            $this->attributes['status'] = $value ?? 2;
        }
    }

    public function getRequestTypeAttribute($value)
    {
        if (is_numeric($value)) {
            return ApplicationRequestType::find($value)?->name ?? $value;
        }
        return $value;
    }

    public function setRequestTypeAttribute($value)
    {
        if (is_string($value) && !is_numeric($value)) {
            $id = ApplicationRequestType::where('name', $value)->value('id');
            $this->attributes['request_type'] = $id ?? 1;
        } else {
            $this->attributes['request_type'] = $value;
        }
    }

    public function getStatusIdAttribute()
    {
        return $this->attributes['status'] ?? null;
    }

    public function getRequestTypeIdAttribute()
    {
        return $this->attributes['request_type'] ?? null;
    }

    public function requestTypeRelation()
    {
        return $this->belongsTo(ApplicationRequestType::class, 'request_type');
    }

    public function requestType()
    {
        return $this->belongsTo(ApplicationRequestType::class, 'request_type');
    }

    /**
     * التحقق مما إذا كانت المعاملة تتطلب مقابلة وقرار أهلية تدريسية
     */
    public function requiresEligibility(): bool
    {
        $typeName = (string) ($this->request_type ?? $this->requestType?->name ?? '');

        if (str_contains($typeName, 'تطبيقي') || str_contains($typeName, 'سماح') || str_contains($typeName, 'هيئة تدريسية')) {
            return false;
        }

        if (str_contains($typeName, 'نظري') || str_contains($typeName, 'دكتوراه') || str_contains($typeName, 'دكتورة')) {
            return true;
        }

        $rawId = (int) ($this->attributes['request_type'] ?? 0);
        return in_array($rawId, [2, 3, 6, 7], true);
    }

    public function statusRelation()
    {
        return $this->belongsTo(ApplicationStatus::class, 'status');
    }

    public function parentApplication()
    {
        return $this->belongsTo(Application::class, 'parent_application_id');
    }

    public function transferChildren()
    {
        return $this->hasMany(Application::class, 'parent_application_id');
    }

    public function candidate()
    {
        return $this->belongsTo(EquivalenceProfile::class, 'candidate_id');
    }

    /**
     * Alias for candidate relationship for developer ergonomics
     */
    public function profile()
    {
        return $this->belongsTo(EquivalenceProfile::class, 'candidate_id');
    }

    /**
     * Check if application is finalized / archived
     */
    public function isArchived(): bool
    {
        $status = $this->attributes['status'] ?? $this->status;
        return in_array($status, [8, '8', 'تم الصدور', 9, '9', 'مرفوض', 10, '10']);
    }

    public function workUniversity()
    {
        return $this->belongsTo(LookupUniversity::class, 'work_university_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function courses()
    {
        return $this->hasMany(ApplicationCourse::class, 'application_id');
    }

    public function educations()
    {
        return $this->hasMany(Education::class, 'application_id');
    }

    public function experiences()
    {
        return $this->hasMany(ApplicationExperience::class, 'application_id');
    }

    /**
     * Total days of verified teaching experience
     */
    public function getTotalExperienceDaysAttribute(): int
    {
        return (int) $this->experiences->sum(function ($exp) {
            if ($exp->duration_days > 0) {
                return $exp->duration_days;
            }
            if ($exp->start_date && $exp->end_date) {
                $start = \Carbon\Carbon::parse($exp->start_date);
                $end = \Carbon\Carbon::parse($exp->end_date);
                return max(0, $start->diffInDays($end));
            }
            return 0;
        });
    }

    /**
     * Check if cumulative experience >= 2 years (730 days)
     */
    public function getHasTwoYearsExperienceAttribute(): bool
    {
        return $this->total_experience_days >= 730;
    }

    public function messages()
    {
        return $this->hasMany(ApplicationMessage::class, 'application_id')->orderBy('created_at', 'asc');
    }

    public function decisions()
    {
        return $this->hasMany(ApplicationDecision::class, 'application_id')->latest();
    }

    public function latestDecision()
    {
        return $this->hasOne(ApplicationDecision::class, 'application_id')->latestOfMany();
    }

    /**
     * Send automated notification message and trigger SMS notification to university on status changes.
     */
    public function notifyUniversityOfStatusChange($newStatus, $customNotes = null, $senderId = null)
    {
        $candidateName = $this->candidate ? $this->candidate->full_name : 'المرشح';
        $appNo = $this->application_no ?: $this->id;
        $senderId = $senderId ?: (\Illuminate\Support\Facades\Auth::id() ?? 1);

        $messages = [
            'تحت التدقيق الأولي' => "🔍 [تحديث حالة الطلب]: تم تغيير حالة المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى (تحت التدقيق الأولي). يجري تدقيق ومراجعة البيانات والوثائق أصولاً.",
            'بانتظار لجنة إنتاج علمي' => "🔬 [تحديث حالة الطلب]: تم تحويل المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى (بانتظار لجنة الإنتاج العلمي) لتقييم الأبحاث والنتاج العلمي.",
            'بانتظار لجنة الإنتاج العلمي' => "🔬 [تحديث حالة الطلب]: تم تحويل المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى (بانتظار لجنة الإنتاج العلمي) لتقييم الأبحاث والنتاج العلمي.",
            'بانتظار المقابلة' => "👥 [تحديث حالة الطلب]: تم تحويل المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى (بانتظار المقابلة). سيتم إشعاركم بموعد ومكان المقابلة فور تثبيتها.",
            'بانتظار الوثائق' => "⚠️ [إشعار رسمي - بانتظار الوثائق والتعديل]: تم تحويل حالة المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى (بانتظار الوثائق). يرجى استكمال وتعديل الوثائق والمستندات المطلوبة عبر زر التعديل ثم إرسال الطلب مجدداً للوزارة.",
            'بانتظار إصدار القرار' => "📋 [تحديث حالة الطلب]: تم تحويل المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى (بانتظار إصدار القرار) بعد استيفاء الشروط واجتياز المتطلبات بنجاح.",
            'بانتظار صدور القرار' => "📋 [تحديث حالة الطلب]: تم تحويل المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى (بانتظار إصدار القرار) بعد استيفاء الشروط واجتياز المتطلبات بنجاح.",
            'تم الصدور' => "📜 [إشعار رسمي - صدور قرار التعادل]: تم صدور قرار معادلة الشهادة العلمية رسمياً للطلب رقم (#{$appNo}) للمرشح ({$candidateName}). يمكنك الاطلاع على نسخة القرار وتحميلها أصولاً.",
            'مرفوض' => "❌ [تحديث حالة الطلب]: تم رفض طلب التعادل رقم (#{$appNo}) للمرشح ({$candidateName}).",
            'لجنة عامة' => "🏛️ [تحديث حالة الطلب]: تم إحالة المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى اللجنة العامة للنظر فيها.",
            'معلق' => "⏳ [تحديث حالة الطلب]: تم تعليق المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) ريثما يتم استكمال الإجراءات.",
        ];

        $msgText = $messages[$newStatus] ?? "🔔 [تحديث حالة الطلب]: تم تغيير حالة المعاملة رقم (#{$appNo}) للمرشح ({$candidateName}) إلى ({$newStatus}).";
        if ($customNotes) {
            $msgText .= " (ملاحظات: " . $customNotes . ")";
        }

        // 1. Create in-app ApplicationMessage for University
        ApplicationMessage::create([
            'application_id' => $this->id,
            'sender_id'      => $senderId,
            'message'        => $msgText,
            'is_read'        => false,
        ]);

        // 2. Dispatch / Log SMS Notification
        $uniName = $this->workUniversity->name ?? 'الجامعة الخاصة المعنية';
        \Illuminate\Support\Facades\Log::info("[SMS Notification] Sent to University ({$uniName}) for App #{$appNo}: {$msgText}");
    }
    /**
 * هل تم اعتماد المسار النظري من قبل اللجنة؟
 */
    public function isTheoreticalApproved(): bool
    {
        return $this->committee_track === 'theoretical' || $this->committee_track === 'نظري';
    }

    /**
     * هل تم اعتماد المسار التطبيقي من قبل اللجنة؟
     */
    public function isAppliedApproved(): bool
    {
        return $this->committee_track === 'applied' || $this->committee_track === 'تطبيقي';
    }

    /**
     * هل تم اعتماد الإنتاج العلمي من قبل اللجنة؟
     */
    public function isScientificProductionApproved(): bool
    {
        return (bool) $this->scientific_production_approved;
    }

    /**
     * مصفوفة التصريف اللغوي المذكر والمؤنث للمرشح
     */
    public function getGenderDictionary()
    {
        return \App\Http\Controllers\Admin\GeneratedDecisionController::getGenderDictionary($this);
    }

    /**
     * حساب مجموع فترات الإقامة المتفرقة بالأعوام والأشهر والأيام التقويمية بدقة
     */
    public static function calculateResidenceSummary($residences): array
    {
        $totalDays = 0;
        $sumY = 0;
        $sumM = 0;
        $sumD = 0;
        $count = 0;

        if ($residences) {
            foreach ($residences as $r) {
                if (!empty($r->entry_date) && !empty($r->exit_date)) {
                    $in = \Carbon\Carbon::parse($r->entry_date)->startOfDay();
                    $out = \Carbon\Carbon::parse($r->exit_date)->startOfDay();
                    if ($out->gte($in)) {
                        $diff = $in->diff($out);
                        $sumY += $diff->y;
                        $sumM += $diff->m;
                        $sumD += $diff->d;
                        $totalDays += (int) $in->diffInDays($out);
                        $count++;
                    }
                }
            }
        }

        if ($sumD >= 30) {
            $sumM += intdiv($sumD, 30);
            $sumD = $sumD % 30;
        }
        if ($sumM >= 12) {
            $sumY += intdiv($sumM, 12);
            $sumM = $sumM % 12;
        }

        return [
            'years'           => $sumY,
            'months'          => $sumM,
            'days'            => $sumD,
            'total_days'      => $totalDays,
            'movements_count' => $count,
        ];
    }
}

