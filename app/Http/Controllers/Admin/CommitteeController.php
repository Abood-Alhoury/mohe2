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
        // Applications requiring General Committee decision (status 'لجنة عامة' or 'معلق')
        $committeeApps = Application::with(['candidate', 'workUniversity', 'educations.level'])
            ->whereIn('status', ['لجنة عامة', 'معلق'])
            ->latest()
            ->get();

        return view('admin.committee.index', compact('committeeApps'));
    }

    public function decide(Request $request, $id)
    {
        $request->validate([
            'decision' => 'required|in:موافقة,رفض,بانتظار إصدار القرار,مرفوض,تم الصدور,قيد الدراسة,معلق',
        ]);

        $app = Application::findOrFail($id);

        if ($request->decision === 'موافقة' || $request->decision === 'بانتظار إصدار القرار') {
            $app->status = 'بانتظار إصدار القرار';
            $msg = 'تم إقرار الموافقة من اللجنة العامة وتحويل حالة الطلب رقم (' . ($app->application_no ?? $app->id) . ') بنجاح إلى (بانتظار إصدار القرار).';
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
