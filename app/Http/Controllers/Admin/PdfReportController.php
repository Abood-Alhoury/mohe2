<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\PdfMergerService;

class PdfReportController extends Controller
{
    public function show($id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'courses',
            'experiences',
            'parentApplication.workUniversity',
            'parentApplication.latestDecision',
            'parentApplication.courses',
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
        $phdEd = $application->educations->first(function($e) {
            return ($e->level?->name === 'دكتوراه' || $e->education_level_id == 3) && !str_contains($e->thesis_title ?? '', 'هيئة تدريسية') && !str_contains($e->thesis_title ?? '', 'مركز بحوث');
        }) ?? $application->educations->where('level.name', 'دكتوراه')->first();

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
            'experiences',
            'parentApplication.workUniversity',
            'parentApplication.latestDecision',
            'parentApplication.courses',
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
        $phdEd        = $application->educations->first(function($e) {
            return ($e->level?->name === 'دكتوراه' || $e->education_level_id == 3) && !str_contains($e->thesis_title ?? '', 'هيئة تدريسية') && !str_contains($e->thesis_title ?? '', 'مركز بحوث');
        }) ?? $application->educations->where('level.name', 'دكتوراه')->first();

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

        // 3. Load the pre-processed HTML into DomPDF and output with explicit Content-Length
        $pdf = Pdf::loadHtml($html)->setPaper('a4', 'portrait');
        $pdfContent = $pdf->output();

        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? ('App_' . $application->id));
        $candidateName = $candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        $fileName = 'Mozhakkara_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Length' => strlen($pdfContent),
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }


    // Consolidated Viewer: Page 1 Mozhakkara report + Page 2+ attachments & decisions
    public function consolidatedView($id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'courses',
            'experiences',
            'parentApplication.workUniversity',
            'parentApplication.latestDecision',
            'parentApplication.courses',
            'parentApplication.educations.attachments.attachmentType',
            'parentApplication.decisions',
            'educations.level',
            'educations.country',
            'educations.university',
            'educations.residences',
            'educations.attachments.attachmentType',
            'decisions',
            'latestDecision',
        ])->findOrFail($id);

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd        = $application->educations->first(function($e) {
            return ($e->level?->name === 'دكتوراه' || $e->education_level_id == 3) && !str_contains($e->thesis_title ?? '', 'هيئة تدريسية') && !str_contains($e->thesis_title ?? '', 'مركز بحوث');
        }) ?? $application->educations->where('level.name', 'دكتوراه')->first();

        return view('admin.reports.consolidated', compact(
            'application', 'candidate', 'highSchoolEd', 'bachelorEd', 'diplomaEd', 'masterEd', 'phdEd'
        ));
    }

    public function downloadConsolidatedPdf($id)
    {
        $application = Application::with([
            'candidate.nationality',
            'workUniversity',
            'courses',
            'experiences',
            'parentApplication.workUniversity',
            'parentApplication.latestDecision',
            'parentApplication.courses',
            'parentApplication.educations.attachments.attachmentType',
            'parentApplication.decisions',
            'educations.level',
            'educations.country',
            'educations.university',
            'educations.residences',
            'educations.attachments.attachmentType',
            'decisions',
            'latestDecision',
        ])->findOrFail($id);

        $candidate = $application->candidate;

        $highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
        $bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
        $diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
        $masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
        $phdEd        = $application->educations->first(function($e) {
            return ($e->level?->name === 'دكتوراه' || $e->education_level_id == 3) && !str_contains($e->thesis_title ?? '', 'هيئة تدريسية') && !str_contains($e->thesis_title ?? '', 'مركز بحوث');
        }) ?? $application->educations->where('level.name', 'دكتوراه')->first();

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
        $mozhakkaraTmpPath = storage_path('app/tmp_mozhakkara_' . $id . '_' . uniqid() . '.pdf');
        file_put_contents($mozhakkaraTmpPath, $mozhakkaraPdf->output());

        // 2. Build consolidated PDF via PdfMergerService (merges Mozhakkara + decisions + all attachments)
        $mergedContent = PdfMergerService::buildConsolidatedPdf($mozhakkaraTmpPath, $application);
        @unlink($mozhakkaraTmpPath);

        if (!$mergedContent) {
            $mergedContent = $mozhakkaraPdf->output();
        }

        // 3. Output merged PDF with clean, descriptive filename
        $safeAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $application->application_no ?? ('App_' . $application->id));
        $candidateName = $candidate ? preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $candidate->full_name) : '';
        $cleanCandidateName = trim(preg_replace('/\s+/', '_', $candidateName));

        $fileName = 'Merged_Package_' . $safeAppNo . ($cleanCandidateName ? '_' . $cleanCandidateName : '') . '.pdf';

        $disposition = request()->has('download') ? 'attachment' : 'inline';

        return response($mergedContent, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $fileName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName),
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0',
            'Pragma'              => 'public',
        ]);
    }
}
