<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$arabic = new \ArPHP\I18N\Arabic();

$testStrings = [
    "البيانات الشخصية للمرشح :",
    "نوع الطلب :",
    "تعادل للمرة الأولى - ماجستير سوري",
    "ID :",
    "8",
    "اسم المرشح : عبد الرحمن نعمان",
    "الرقم الوطني : 01030040123",
];

echo "--- TESTING DIFFERENT utf8Glyphs PARAMETERS ---\n\n";

foreach ($testStrings as $str) {
    echo "ORIGINAL: $str\n";
    echo "Default (1000000)                : " . $arabic->utf8Glyphs($str, 1000000) . "\n";
    echo "hindo=false (1000000, false)     : " . $arabic->utf8Glyphs($str, 1000000, false) . "\n";
    echo "forcertl=true (1000000, false, t): " . $arabic->utf8Glyphs($str, 1000000, false, true) . "\n";
    echo "---------------------------------------------------------\n";
}
