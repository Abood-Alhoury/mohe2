<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

$arabic = new \ArPHP\I18N\Arabic();

$testStrings = [
    "البيانات الشخصية للمرشح :",
    "نوع الطلب : تعادل للمرة الأولى - ماجستير سوري",
    "ID : 8",
    "اسم المرشح : عبد الرحمن نعمان",
    "الرقم الوطني : 01030040123",
    "المرشح للعمل في قسم : علوم ويب | في كلية : هندسة المعلوماتية | في جامعة : جامعة دمشق"
];

echo "--- TESTING ArPHP utf8Glyphs ---\n";
foreach ($testStrings as $str) {
    $shaped = $arabic->utf8Glyphs($str);
    echo "ORIGINAL: $str\n";
    echo "SHAPED  : $shaped\n\n";
}
