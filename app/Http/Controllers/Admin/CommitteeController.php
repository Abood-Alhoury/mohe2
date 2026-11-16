<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    /**
     * عرض قائمة طلبات اللجنة العامة
     */
    public function index(Request $request)
    {
        $committeeApps = Application::where('status', 'لجنة عامة')
            ->orWhere('status', 4)
            ->with(['candidate', 'workUniversity', 'educations.level', 'educations.university', 'educations.country'])
            ->latest('id')
            ->get();

        $totalCount = $committeeApps->count();
        $pendingCount = $totalCount;
        $approvedCount = Application::whereIn('status', ['بانتظار المقابلة', 'بانتظار إصدار القرار', 'بانتظار لجنة إنتاج علمي', 'تم الصدور'])->count();
        $rejectedCount = Application::where('status', 'مرفوض')->count();

        return view('admin.committee.index', compact('committeeApps', 'totalCount', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    /**
     * اتخاذ وحفظ قرار اللجنة العامة (Dropdown)
     */
    public function decide(Request $request, $id)
    {
        $application = Application::with(['candidate', 'educations'])->findOrFail($id);

        $action = $request->input('decision_action') ?? $request->input('decision');
        $reqType = $application->request_type ?? '';
        $isForeignDoctorate = str_contains($reqType, 'دكتورة خارجية') || str_contains($reqType, 'دكتوراه خارجية')
            || ((str_contains($reqType, 'دكتوراه') || str_contains($reqType, 'دكتورة')) && str_contains($reqType, 'خارجي'));
        $isForeignMaster = !$isForeignDoctorate && (str_contains($reqType, 'خارجي') || str_contains($reqType, 'غير سوري'));

        // 1. في حال الرفض
        if ($action === 'rejected' || $action === 'رفض') {
            $reason = $request->input('rejection_reason') ?: 'تم رفض المعادلة بقرار من اللجنة العامة لعدم استيفاء الشروط.';

            $application->update([
                'status'              => 'مرفوض',
                'committee_track'     => 'rejected',
                'experience_approved' => false,
                'rejection_reason'    => $reason,
            ]);

            $application->notifyUniversityOfStatusChange('مرفوض', $reason);

            return redirect()->route('admin.committee.index')
                ->with('action_banner', [
                    'variant'        => 'danger',
                    'title'          => 'تم تسجيل عدم الموافقة للطلب (#'.($application->application_no ?? $application->id).') ونقله إلى إصدار القرارات',
                    'message'        => 'اختفى الطلب من جدول اللجنة العامة وأصبح جاهزاً في (إصدار القرارات ← قرارات عدم الموافقة) لتوليد الكتاب وتوقيعه وختمه ثم رفعه وأرشفته.',
                    'generate_url'   => route('admin.reports.generate_decision', $application->id),
                    'generate_label' => 'توليد / Word كتاب عدم الموافقة',
                    'issue_url'      => route('admin.rejected_decisions.index', ['app_id' => $application->id]),
                    'issue_label'    => 'الانتقال لإصدار القرارات (رفع المختوم)',
                ]);
        }

        // 2. معالجة خيارات الدكتوراه الخارجية
        if ($isForeignDoctorate) {
            if ($action === 'approved_to_scientific_production' || $action === 'scientific_production') {
                $application->update([
                    'status'              => 'بانتظار لجنة إنتاج علمي',
                    'committee_track'     => 'scientific_production',
                    'rejection_reason'    => null,
                ]);

                $application->notifyUniversityOfStatusChange('بانتظار لجنة إنتاج علمي');
                $msg = 'تمت موافقة اللجنة العامة على إحالة طلب الدكتوراه الخارجية إلى (لجنة الإنتاج العلمي) بنجاح.';
            }
        }
        // 3. معالجة خيارات الماجستير الخارجي
        elseif ($isForeignMaster) {
            // الخيار أ: مقبول (مسار نظري)
            if ($action === 'approved_theoretical') {
                $application->update([
                    'committee_track'     => 'theoretical',
                    'experience_approved' => true,
                    'request_type'        => 'ماجستير خارجي - نظري',
                    'status'              => 'بانتظار المقابلة', // يتطلب مقابلة وأهلية
                    'rejection_reason'    => null,
                ]);
                $application->notifyUniversityOfStatusChange('بانتظار المقابلة');
                $msg = 'تم اعتماد تعادل الماجستير الخارجي (مسار نظري - اعتماد الخبرة) وإحالة المرشح إلى (بانتظار المقابلة).';
            } 
            // الخيار ب: ماجستير تطبيقي (سواء كان تطبيقي من البداية وقُبل، أو كان نظري وتم تحويله لتطبيقي)
            elseif ($action === 'approved_applied' || $action === 'applied') {
                $application->update([
                    'committee_track'     => 'applied',
                    'experience_approved' => false,
                    'request_type'        => 'ماجستير خارجي - تطبيقي',
                    'status'              => 'بانتظار إصدار القرار', // مباشرة بدون مقابلة
                    'rejection_reason'    => null,
                ]);
                $application->notifyUniversityOfStatusChange('بانتظار إصدار القرار');
                $msg = 'تم اعتماد تعادل الماجستير الخارجي (مسار تطبيقي - عضو هيئة فنية) ونقله مباشرة إلى (بانتظار إصدار القرار).';
            }
        } else {
            // 4. بقية الطلبات السورية (الماجستير النظري والدكتوراه -> المقابلة والأهلية، التطبيقي والسماح -> إصدار القرار)
            $nextStatus = $application->requiresEligibility() ? 'بانتظار المقابلة' : 'بانتظار إصدار القرار';

            $application->update([
                'status'           => $nextStatus,
                'rejection_reason' => null,
            ]);
            $application->notifyUniversityOfStatusChange($nextStatus);
            $msg = 'تم إقرار موافقة اللجنة العامة على الطلب وتحويله إلى (' . $nextStatus . ') بنجاح.';
        }

        return redirect()->route('admin.committee.index')->with('success', $msg ?? 'تم حفظ قرار اللجنة بنجاح.');
    }
}