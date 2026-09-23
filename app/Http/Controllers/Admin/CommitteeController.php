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
            ->with(['candidate', 'workUniversity', 'educations.level'])
            ->latest('id')
            ->get();

        return view('admin.committee.index', compact('committeeApps'));
    }

    /**
     * اتخاذ وحفظ قرار اللجنة العامة (Dropdown)
     */
    public function decide(Request $request, $id)
    {
        $application = Application::with(['candidate', 'educations'])->findOrFail($id);

        $action = $request->input('decision_action') ?? $request->input('decision');
        $isForeignMaster = str_contains($application->request_type ?? '', 'خارجي') || str_contains($application->request_type ?? '', 'غير سوري');

        // 1. في حال الرفض
        if ($action === 'rejected' || $action === 'رفض') {
            $reason = $request->input('rejection_reason') ?: 'تم رفض المعادلة بقرار من اللجنة العامة لعدم استيفاء الشروط.';

            $application->update([
                'status'              => 'مرفوض',
                'committee_track'     => 'rejected',
                'experience_approved' => false,
                'rejection_reason'    => $reason,
            ]);

            return redirect()->route('admin.committee.index')->with('success', 'تم تسجيل قرار الرفض للطلب رقم: ' . $application->application_no);
        }

        // 2. معالجة خيارات الماجستير الخارجي
        if ($isForeignMaster) {
            // الخيار أ: مقبول (مسار نظري)
            if ($action === 'approved_theoretical') {
                $application->update([
                    'committee_track'     => 'theoretical',
                    'experience_approved' => true,
                    'request_type'        => 'ماجستير خارجي - نظري',
                    'status'              => 'بانتظار المقابلة', // يتطلب مقابلة وأهلية
                    'rejection_reason'    => null,
                ]);
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
                $msg = 'تم اعتماد تعادل الماجستير الخارجي (مسار تطبيقي - عضو هيئة فنية) ونقله مباشرة إلى (بانتظار إصدار القرار).';
            }
        } else {
            // 3. بقية الطلبات السورية
            $isDoctorate = str_contains($application->request_type ?? '', 'دكتوراه');
            $nextStatus = $isDoctorate ? 'بانتظار المقابلة' : 'بانتظار إصدار القرار';

            $application->update([
                'status'           => $nextStatus,
                'rejection_reason' => null,
            ]);
            $msg = 'تم إقرار موافقة اللجنة العامة على الطلب وتحويله إلى (' . $nextStatus . ') بنجاح.';
        }

        return redirect()->route('admin.committee.index')->with('success', $msg ?? 'تم حفظ قرار اللجنة بنجاح.');
    }
}