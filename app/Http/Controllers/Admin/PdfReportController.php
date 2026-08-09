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

        // 2. Apply ArPHP glyph shaping
        try {
            $arabic = new \ArPHP\I18N\Arabic();
            $styleBlocks = [];
            $html = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
                $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
                $styleBlocks[$placeholder] = $m[0];
                return $placeholder;
            }, $html);

            $p = $arabic->arIdentify($html);
            if ($p && count($p) > 0) {
                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]), 1000000);
                    $html   = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }
            }

            foreach ($styleBlocks as $placeholder => $block) {
                $html = str_replace($placeholder, $block, $html);
            }
        } catch (\Throwable $e) {}

        // 3. Load the pre-processed HTML into DomPDF
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

    public function downloadConsolidatedPdf($id)
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
        $bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd        = $application->educations->where('level.name', 'دكتوراه')->first();

        // 1. Generate Mozhakkara PDF (page 1) using DomPDF
        $html = view('admin.reports.pdf_template', compact(
            'application', 'candidate', 'highSchoolEd', 'bachelorEd', 'diplomaEd', 'masterEd', 'phdEd'
        ))->render();

        try {
            $arabic = new \ArPHP\I18N\Arabic();
            $styleBlocks = [];
            $html = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
                $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
                $styleBlocks[$placeholder] = $m[0];
                return $placeholder;
            }, $html);

            $p = $arabic->arIdentify($html);
            if ($p && count($p) > 0) {
                for ($i = count($p) - 1; $i >= 0; $i -= 2) {
                    $utf8ar = $arabic->utf8Glyphs(substr($html, $p[$i - 1], $p[$i] - $p[$i - 1]), 1000000);
                    $html   = substr_replace($html, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
                }
            }

            foreach ($styleBlocks as $placeholder => $block) {
                $html = str_replace($placeholder, $block, $html);
            }
        } catch (\Throwable $e) {}

        $mozhakkaraPdf = Pdf::loadHtml($html)->setPaper('a4', 'portrait');
        $mozhakkaraTmpPath = storage_path('app/tmp_mozhakkara_' . $id . '.pdf');
        file_put_contents($mozhakkaraTmpPath, $mozhakkaraPdf->output());

        // 2. Collect all uploaded PDF attachment file paths
        $attachmentPaths = [];
        foreach ($application->educations as $ed) {
            foreach ($ed->attachments as $att) {
                $fullPath = storage_path('app/public/' . $att->file_path);
                if (file_exists($fullPath) && strtolower(pathinfo($fullPath, PATHINFO_EXTENSION)) === 'pdf') {
                    $attachmentPaths[] = $fullPath;
                }
            }
        }

        // 3. Merge all PDFs using FPDI: Mozhakkara first, then each attachment
        $merger = new \setasign\Fpdi\Fpdi();

        // Add Mozhakkara pages
        $mozhakkaraPageCount = $merger->setSourceFile($mozhakkaraTmpPath);
        for ($p = 1; $p <= $mozhakkaraPageCount; $p++) {
            $tplId = $merger->importPage($p);
            $size = $merger->getTemplateSize($tplId);
            $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $merger->useTemplate($tplId);
        }

        // Add each attachment PDF pages
        foreach ($attachmentPaths as $pdfPath) {
            try {
                $pageCount = $merger->setSourceFile($pdfPath);
                for ($p = 1; $p <= $pageCount; $p++) {
                    $tplId = $merger->importPage($p);
                    $size = $merger->getTemplateSize($tplId);
                    $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $merger->useTemplate($tplId);
                }
            } catch (\Throwable $e) {
                // Skip corrupted/unreadable PDFs
                continue;
            }
        }

        // 4. Output merged PDF
        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? $application->id);
        $fileName  = 'Merged_Package_' . $safeAppNo . '.pdf';

        // Clean up temp file
        $mergedContent = $merger->Output('S');
        @unlink($mozhakkaraTmpPath);

        return response($mergedContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}

