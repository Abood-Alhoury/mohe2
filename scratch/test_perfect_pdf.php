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

// Test Template with dir="ltr" and ArPHP
$html = view('admin.reports.pdf_template', compact(
    'application', 'candidate', 'highSchoolEd', 'bachelorEd', 'diplomaEd', 'masterEd', 'phdEd'
))->render();

$arabic = new \ArPHP\I18N\Arabic();
$styleBlocks = [];
$htmlAr = preg_replace_callback('/<style[^>]*>.*?<\/style>/si', function ($m) use (&$styleBlocks) {
    $placeholder = '___STYLE_BLOCK_' . count($styleBlocks) . '___';
    $styleBlocks[$placeholder] = $m[0];
    return $placeholder;
}, $html);

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

$pdf = Pdf::loadHtml($htmlAr)->setPaper('a4', 'portrait');
file_put_contents(__DIR__ . '/perfect_mozhakkara.pdf', $pdf->output());

echo "Generated perfect_mozhakkara.pdf successfully!\n";
