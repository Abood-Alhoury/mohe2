<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Application;
use App\Models\ApplicationMessage;

class CommitteeController extends Controller
{
    public function index()
    {
        // Applications requiring General Committee decision (status 'لجنة عامة' or 'معلق') - only for allowed full equivalence types
        $committeeApps = Application::with(['candidate', 'workUniversity', 'educations.level'])
            ->whereIn('status', ['لجنة عامة', 'معلق'])
            ->where('request_type', 'not like', '%سماح%')
            ->where('request_type', 'not like', '%تدريسية%')
            ->where('request_type', 'not like', '%بحوث%')
            ->where(function($q) {
                $q->where('request_type', 'not like', '%تطبيقي%')
                  ->orWhere('request_type', 'like', '%خارجي%');
            })
            ->latest()
            ->get();

        return view('admin.committee.index', compact('committeeApps'));
    }

    public function decide(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:موافقة,رفض,بانتظار إصدار القرار,بانتظار المقابلة,مرفوض,تم الصدور,قيد الدراسة,معلق',
        ]);

        $app = Application::with('educations')->findOrFail($id);

        if ($request->decision === 'موافقة' || $request->decision === 'بانتظار إصدار القرار') {
            $isForeignTheoretical = str_contains($app->request_type, 'نظري') || 
                (str_contains($app->request_type, 'خارجي') && optional($app->educations->where('education_level_id', 2)->first())->experience_from_year !== null);

            if ($isForeignTheoretical) {
                $app->status = 'بانتظار المقابلة';
                $msg = 'تمت الموافقة من اللجنة العامة لمعاملة الماجستير الخارجي (المسار النظري) وتحويل الطلب رقم (' . ($app->application_no ?? $app->id) . ') إلى (بانتظار المقابلة) لتحديد موعد مقابلة الأهلية.';
            } else {
                $app->status = 'بانتظار إصدار القرار';
                $msg = 'تم إقرار الموافقة من اللجنة العامة وتحويل حالة الطلب رقم (' . ($app->application_no ?? $app->id) . ') بنجاح إلى (بانتظار إصدار القرار).';
            }
        } elseif ($request->decision === 'رفض' || $request->decision === 'مرفوض') {
            $app->status = 'مرفوض';
            $msg = 'تم إقرار الرفض من اللجنة العامة وتحويل حالة الطلب رقم (' . ($app->application_no ?? $app->id) . ') بنجاح إلى (مرفوض).';
        } else {
            $app->status = $request->decision;
            $msg = 'تم تحديث وضع الطلب بنجاح إلى (' . $app->status . ')';
        }

        $app->save();

        // Automated notification to university
        $app->notifyUniversityOfStatusChange($app->status);

        return redirect()->route('admin.committee.index')->with('success', $msg);
    }
}
