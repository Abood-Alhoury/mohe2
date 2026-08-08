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
        $bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd        = $application->educations->where('level.name', 'دكتوراه')->first();

        // 1. Render Blade view to an HTML string
        $html = view('admin.reports.pdf_template', compact(
            'application',
            'candidate',
            'highSchoolEd',
            'bachelorEd',
            'diplomaEd',
            'masterEd',
            'phdEd'
        ))->render();

        // 2. Apply ArPHP Arabic glyph shaping — protect <style> blocks first
        try {
            $arabic = new \ArPHP\I18N\Arabic();

            // Extract <style> blocks so ArPHP doesn't mangle CSS
            $styleBlocks = [];
            $html = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
                $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
                $styleBlocks[$placeholder] = $m[0];
                return $placeholder;
            }, $html);

            // Apply glyph shaping to the HTML body (without CSS)
            $p = $arabic->arIdentify($html);
            if ($p && count($p) > 0) {
                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]), 1000000);
                    $html   = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }
            }

            // Restore the CSS blocks untouched
            foreach ($styleBlocks as $placeholder => $block) {
                $html = str_replace($placeholder, $block, $html);
            }
        } catch (\Throwable $e) {
            // If ArPHP fails for any reason, continue without reshaping
        }

        // 3. Load the pre-processed HTML into dompdf
        $pdf = Pdf::loadHtml($html)->setPaper('a4', 'portrait');

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? $application->id);
        $fileName  = 'Mozhakkara_' . $safeAppNo . '.pdf';

        return $pdf->stream($fileName);
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
