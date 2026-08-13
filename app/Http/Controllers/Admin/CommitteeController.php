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
        $candidateName = $app->candidate ? $app->candidate->full_name : '';
        if ($app->status === 'بانتظار إصدار القرار') {
            ApplicationMessage::create([
                'application_id' => $app->id,
                'sender_id' => Auth::id() ?? 1,
                'message' => "✅ [إشعار اللجنة العامة]: تمت موافقة اللجنة العامة على طلب تعادل الشهادة العلمية رقم (#{$app->application_no}) للمرشح ({$candidateName})، وتحويل المعاملة إلى (بانتظار إصدار القرار).",
                'is_read' => false,
            ]);
        } elseif ($app->status === 'مرفوض') {
            ApplicationMessage::create([
                'application_id' => $app->id,
                'sender_id' => Auth::id() ?? 1,
                'message' => "❌ [إشعار اللجنة العامة]: صدر قرار اللجنة العامة برفض طلب التعادل رقم (#{$app->application_no}) للمرشح ({$candidateName}).",
                'is_read' => false,
            ]);
        }

        return redirect()->route('admin.committee.index')->with('success', $msg);
    }
}
