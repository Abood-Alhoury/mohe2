<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Application;
use Barryvdh\DomPDF\Facade\Pdf;

$application = Application::with([
    'candidate.nationality',
    'workUniversity',
    'courses',
    'educations.level',
    'educations.country',
    'educations.university',
    'educations.residences'
])->first();

$candidate = $application->candidate;

$highSchoolEd = $application->educations->where('level.name', 'ثانوية عامة')->first();
$bachelorEd   = $application->educations->where('level.name', 'إجازة جامعية')->first();
$diplomaEd    = $application->educations->where('level.name', 'دبلوم دراسات عليا')->first();
$masterEd     = $application->educations->where('level.name', 'ماجستير')->first();
$phdEd        = $application->educations->where('level.name', 'دكتوراه')->first();

$htmlRaw = view('admin.reports.pdf_template', compact(
    'application', 'candidate', 'highSchoolEd', 'bachelorEd', 'diplomaEd', 'masterEd', 'phdEd'
))->render();

// Test 1: Raw HTML (No ArPHP) with DomPDF
$pdf1 = Pdf::loadHtml($htmlRaw)->setPaper('a4', 'portrait');
file_put_contents(__DIR__ . '/pdf_test1_raw.pdf', $pdf1->output());

// Test 2: ArPHP with LTR body in HTML (how old code worked)
$arabic = new \ArPHP\I18N\Arabic();
$htmlArLtr = str_replace('dir="rtl"', 'dir="ltr"', $htmlRaw);
$htmlArLtr = str_replace('direction: rtl;', 'direction: ltr;', $htmlArLtr);

$styleBlocks = [];
$htmlArLtrClean = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
    $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
    $styleBlocks[$placeholder] = $m[0];
    return $placeholder;
}, $htmlArLtr);

$p = $arabic->arIdentify($htmlArLtrClean);
if ($p && count($p) > 0) {
    for ($i = count($p) - 1; $i >= 0; $i -= 2) {
        $utf8ar = $arabic->utf8Glyphs(substr($htmlArLtrClean, $p[$i - 1], $p[$i] - $p[$i - 1]), 1000000);
        $htmlArLtrClean = substr_replace($htmlArLtrClean, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
    }
}
foreach ($styleBlocks as $placeholder => $block) {
    $htmlArLtrClean = str_replace($placeholder, $block, $htmlArLtrClean);
}

$pdf2 = Pdf::loadHtml($htmlArLtrClean)->setPaper('a4', 'portrait');
file_put_contents(__DIR__ . '/pdf_test2_arphp_ltr.pdf', $pdf2->output());

echo "Done generating test PDFs in scratch/\n";
