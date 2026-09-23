<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;

class ScientificProductionController extends Controller
{
    /**
     * Display listing of foreign doctorate applications awaiting scientific production evaluation.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = Application::with([
            'candidate.nationality',
            'workUniversity',
            'educations.level',
            'educations.country',
            'educations.attachments.attachmentType',
        ])
        ->where('status', 'بانتظار لجنة إنتاج علمي')
        ->where(function($q) {
            $q->where('request_type', 'like', '%دكتور%خارج%')
              ->orWhere('request_type', 'دكتورة خارجية')
              ->orWhere('request_type', 7);
        });

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('application_no', 'like', "%{$search}%")
                  ->orWhere('new_uni_request_no', 'like', "%{$search}%")
                  ->orWhereHas('candidate', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('national_id', 'like', "%{$search}%");
                  });
            });
        }

        $applications = $query->latest('id')->paginate(15);
        $totalCount = $applications->total();

        return view('admin.scientific_production.index', compact('applications', 'search', 'totalCount'));
    }

    /**
     * Record decision of Scientific Production Committee.
     */
    public function decide(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:approved,rejected',
            'decision_no' => 'nullable|string|max:100',
            'decision_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'rejection_reason' => 'required_if:decision,rejected|nullable|string',
        ]);

        $application = Application::with(['candidate', 'workUniversity'])->findOrFail($id);

        if ($request->decision === 'approved') {
            $application->update([
                'status' => 'بانتظار المقابلة',
                'scientific_production_approved' => true,
                'scientific_production_decision_no' => $request->decision_no,
                'scientific_production_date' => $request->decision_date ?: now(),
                'scientific_production_notes' => $request->notes,
                'rejection_reason' => null,
            ]);

            $application->notifyUniversityOfStatusChange('بانتظار المقابلة', 'تمت الموافقة على تقييم الإنتاج العلمي وإحالة المرشح إلى المقابلة الشخصية والأهلية.');

            $msg = 'تم اعتماد الموافقة على الإنتاج العلمي للطلب رقم ' . $application->application_no . ' ونقله بنجاح إلى (بانتظار المقابلة).';
        } else {
            $application->update([
                'status' => 'مرفوض',
                'scientific_production_approved' => false,
                'scientific_production_decision_no' => $request->decision_no,
                'scientific_production_date' => $request->decision_date ?: now(),
                'scientific_production_notes' => $request->notes,
                'rejection_reason' => $request->rejection_reason ?: 'عدم استيفاء معايير الإنتاج العلمي والأبحاث المحكمة المطلوبة.',
            ]);

            $application->notifyUniversityOfStatusChange('مرفوض', $request->rejection_reason);

            $msg = 'تم تسجيل عدم الموافقة على الإنتاج العلمي وتحويل حالة الطلب رقم ' . $application->application_no . ' إلى (مرفوض).';
        }

        return redirect()->route('admin.scientific_production.index')->with('success', $msg);
    }
}
