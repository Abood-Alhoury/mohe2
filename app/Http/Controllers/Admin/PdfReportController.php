<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfReportController extends Controller
{
    public function show($id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'courses',
            'educations.level',
            'educations.country',
            'educations.university',
            'educations.residences',
            'educations.attachments.attachmentType'
        ])->findOrFail($id);

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd = $application->educations->where('level.name', 'دكتوراه')->first();

        return view('admin.reports.mozhakkara', compact(
            'application',
            'candidate',
            'highSchoolEd',
            'bachelorEd',
            'diplomaEd',
            'masterEd',
            'phdEd'
        ));
    }

    public function downloadPdf($id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'courses',
            'educations.level',
            'educations.country',
            'educations.university',
            'educations.residences'
        ])->findOrFail($id);

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd = $application->educations->where('level.name', 'دكتوراه')->first();

        $pdf = Pdf::loadView('admin.reports.pdf_template', compact(
            'application',
            'candidate',
            'highSchoolEd',
            'bachelorEd',
            'diplomaEd',
            'masterEd',
            'phdEd'
        ))->setPaper('a4', 'portrait');

        return $pdf->stream('Mozhakkara_'.$application->application_no.'.pdf');
    }

    // Consolidated Viewer: Page 1 Mozhakkara report + Page 2+ attachments
    public function consolidatedView($id)
    {
        $application = Application::with([
            'candidate',
            'educations.attachments.attachmentType'
        ])->findOrFail($id);

        return view('admin.reports.consolidated', compact('application'));
    }
}
