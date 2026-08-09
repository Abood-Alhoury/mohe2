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

$htmlClean = view('admin.reports.pdf_template', compact(
    'application', 'candidate', 'highSchoolEd', 'bachelorEd', 'diplomaEd', 'masterEd', 'phdEd'
))->render();

// Save clean raw HTML
file_put_contents(__DIR__ . '/clean_template.html', $htmlClean);

// Render PDF with DomPDF without ArPHP
$pdf = Pdf::loadHtml($htmlClean)->setPaper('a4', 'portrait');
file_put_contents(__DIR__ . '/clean_mozhakkara.pdf', $pdf->output());

echo "Generated clean_mozhakkara.pdf successfully!\n";
