<?php

namespace App\Services;

use App\Models\Application;
use Illuminate\Support\Facades\Log;

class PdfMergerService
{
    /**
     * Resolve actual physical file path across potential storage directories.
     */
    public static function resolvePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $rawPath = ltrim($path, '/\\');
        $cleanPath = preg_replace('/^(public\/|storage\/)/', '', $rawPath);

        $possiblePaths = [
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $rawPath),
            public_path('storage/' . $cleanPath),
            public_path($rawPath),
            storage_path('app/public/' . $rawPath),
            base_path($rawPath),
            $path,
        ];

        foreach ($possiblePaths as $p) {
            if (file_exists($p) && is_file($p)) {
                return realpath($p) ?: $p;
            }
        }

        return null;
    }

    /**
     * Collect all attachment and decision paths for an application.
     *
     * @param Application $application
     * @return array ['decisions' => [...], 'attachments' => [...]]
     */
    public static function collectDocuments(Application $application): array
    {
        $application->loadMissing([
            'educations.attachments.attachmentType',
            'parentApplication.educations.attachments.attachmentType',
            'decisions',
            'latestDecision',
            'parentApplication.decisions',
        ]);

        $decisionPaths = [];
        $attachmentPaths = [];

        // 1. Decisions (Current application first, then parent)
        $decisions = collect();
        if ($application->decisions) {
            $decisions = $decisions->concat($application->decisions);
        }
        if ($application->parentApplication && $application->parentApplication->decisions) {
            $decisions = $decisions->concat($application->parentApplication->decisions);
        }

        foreach ($decisions as $dec) {
            // Check main decision file
            if (!empty($dec->file_path)) {
                $res = self::resolvePath($dec->file_path);
                if ($res && !in_array($res, $decisionPaths)) {
                    $decisionPaths[] = $res;
                }
            }
            // Check eligibility decision file
            if (!empty($dec->eligibility_file_path)) {
                $res = self::resolvePath($dec->eligibility_file_path);
                if ($res && !in_array($res, $decisionPaths)) {
                    $decisionPaths[] = $res;
                }
            }
        }

        // 2. Attachments from Educations (in logical creation sequence)
        $attachments = collect();
        if ($application->educations) {
            foreach ($application->educations as $ed) {
                if ($ed->attachments) {
                    $attachments = $attachments->concat($ed->attachments->sortBy('id'));
                }
            }
        }

        if ($application->parentApplication && $application->parentApplication->educations) {
            foreach ($application->parentApplication->educations as $pEd) {
                if ($pEd->attachments) {
                    $attachments = $attachments->concat($pEd->attachments->sortBy('id'));
                }
            }
        }

        foreach ($attachments as $att) {
            if (!empty($att->file_path)) {
                $res = self::resolvePath($att->file_path);
                if ($res && !in_array($res, $attachmentPaths) && !in_array($res, $decisionPaths)) {
                    $attachmentPaths[] = $res;
                }
            }
        }

        // 3. Fallback: Search in storage/app/public/attachments for application files
        $appNo = $application->application_no ?? ('App_' . $application->id);
        $cleanAppNo = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $appNo);
        $candidateFiles = glob(storage_path('app/public/attachments/*' . $cleanAppNo . '*')) ?: [];
        foreach ($candidateFiles as $cFile) {
            $norm = realpath($cFile) ?: $cFile;
            if (is_file($norm) && !in_array($norm, $attachmentPaths) && !in_array($norm, $decisionPaths)) {
                $attachmentPaths[] = $norm;
            }
        }

        return [
            'decisions'   => $decisionPaths,
            'attachments' => $attachmentPaths,
        ];
    }

    /**
     * Merge Mozhakkara with decisions and attachments.
     * Returns the merged PDF binary string or null on failure.
     */
    public static function buildConsolidatedPdf(string $mozhakkaraPdfPath, Application $application): ?string
    {
        $docs = self::collectDocuments($application);
        $allFiles = array_merge([$mozhakkaraPdfPath], $docs['decisions'], $docs['attachments']);

        // Convert any non-PDF files (e.g. JPG, PNG) to temporary PDFs
        $preparedFiles = [];
        $tempFilesToClean = [];

        foreach ($allFiles as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($ext === 'pdf') {
                $preparedFiles[] = $file;
            } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'webp'])) {
                try {
                    $tmpImgPdf = storage_path('app/tmp_img_' . uniqid() . '.pdf');
                    $fpdf = new \setasign\Fpdi\Fpdi('P', 'mm', 'A4');
                    $fpdf->AddPage();
                    $fpdf->Image($file, 10, 10, 190);
                    $fpdf->Output('F', $tmpImgPdf);
                    $preparedFiles[] = $tmpImgPdf;
                    $tempFilesToClean[] = $tmpImgPdf;
                } catch (\Throwable $e) {
                    Log::warning("Could not convert image {$file} to PDF: " . $e->getMessage());
                }
            }
        }

        $mergedPath = storage_path('app/tmp_merged_' . $application->id . '_' . uniqid() . '.pdf');

        $success = self::mergeWithPython($preparedFiles, $mergedPath);
        if (!$success) {
            $success = self::mergeWithFpdi($preparedFiles, $mergedPath);
        }

        // Clean up temporary image PDFs
        foreach ($tempFilesToClean as $tFile) {
            @unlink($tFile);
        }

        if ($success && file_exists($mergedPath)) {
            $content = file_get_contents($mergedPath);
            @unlink($mergedPath);
            return $content;
        }

        return null;
    }

    /**
     * Merge files using Python and pypdf
     */
    protected static function mergeWithPython(array $files, string $outputPath): bool
    {
        $scriptPath = app_path('Services/scripts/merge_runner.py');
        if (!file_exists($scriptPath)) {
            return false;
        }

        // Find python binary
        $pythonExec = self::findPythonExecutable();
        if (!$pythonExec) {
            return false;
        }

        $payloadPath = storage_path('app/tmp_payload_' . uniqid() . '.json');
        file_put_contents($payloadPath, json_encode($files, JSON_UNESCAPED_UNICODE));

        $cmd = escapeshellarg($pythonExec) . ' '
             . escapeshellarg($scriptPath) . ' '
             . escapeshellarg($payloadPath) . ' '
             . escapeshellarg($outputPath);

        $output = [];
        $ret = 0;
        exec($cmd, $output, $ret);

        @unlink($payloadPath);

        if ($ret === 0 && file_exists($outputPath) && filesize($outputPath) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Fallback: merge files using pure PHP FPDI
     */
    protected static function mergeWithFpdi(array $files, string $outputPath): bool
    {
        try {
            $merger = new \setasign\Fpdi\Fpdi();
            $pagesAdded = 0;

            foreach ($files as $file) {
                try {
                    $count = $merger->setSourceFile($file);
                    for ($p = 1; $p <= $count; $p++) {
                        $tpl = $merger->importPage($p);
                        $size = $merger->getTemplateSize($tpl);
                        $merger->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $merger->useTemplate($tpl);
                        $pagesAdded++;
                    }
                } catch (\Throwable $e) {
                    Log::warning("FPDI could not import {$file}: " . $e->getMessage());
                    continue;
                }
            }

            if ($pagesAdded > 0) {
                $merger->Output('F', $outputPath);
                return true;
            }
        } catch (\Throwable $e) {
            Log::error("FPDI merge failed: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Locate python executable
     */
    protected static function findPythonExecutable(): ?string
    {
        $candidates = [
            'python',
            'C:\Python314\python.exe',
            'C:\laragon\bin\python\python-3.13\python.exe',
            'python3',
        ];

        foreach ($candidates as $cand) {
            $testCmd = escapeshellarg($cand) . ' --version';
            $ret = 0;
            @exec($testCmd, $out, $ret);
            if ($ret === 0) {
                return $cand;
            }
        }

        return null;
    }
}
