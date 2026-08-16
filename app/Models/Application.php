<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'status',
        'interview_date',
        'interview_time',
        'interview_notes',
        'user_id',
    ];

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
}
