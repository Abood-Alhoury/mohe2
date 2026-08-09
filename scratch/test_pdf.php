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

// 1. Raw DomPDF
$pdfRaw = Pdf::loadHtml($htmlRaw)->setPaper('a4', 'portrait');
file_put_contents(__DIR__ . '/output_raw.pdf', $pdfRaw->output());
echo "Saved output_raw.pdf\n";

// 2. ArPHP current
$arabic = new \ArPHP\I18N\Arabic();
$styleBlocks = [];
$htmlAr = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
    $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
    $styleBlocks[$placeholder] = $m[0];
    return $placeholder;
}, $htmlRaw);

$p = $arabic->arIdentify($htmlAr);
if ($p && count($p) > 0) {
    for ($i = count($p) - 1; $i >= 0; $i -= 2) {
        $utf8ar = $arabic->utf8Glyphs(substr($htmlAr, $p[$i - 1], $p[$i] - $p[$i - 1]), 1000000);
        $htmlAr = substr_replace($htmlAr, $utf8ar, $p[$i - 1], $p[$i] - $p[$i - 1]);
    }
}
foreach ($styleBlocks as $placeholder => $block) {
    $htmlAr = str_replace($placeholder, $block, $htmlAr);
}

$pdfAr = Pdf::loadHtml($htmlAr)->setPaper('a4', 'portrait');
file_put_contents(__DIR__ . '/output_arphp.pdf', $pdfAr->output());
echo "Saved output_arphp.pdf\n";
