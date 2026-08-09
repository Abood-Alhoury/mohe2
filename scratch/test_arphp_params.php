<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$arabic = new \ArPHP\I18N\Arabic();

$reflection = new ReflectionClass($arabic);
echo "Methods in Arabic class:\n";
foreach ($reflection->getMethods() as $m) {
    if (str_contains(strtolower($m->name), 'glyph') || str_contains(strtolower($m->name), 'utf8') || str_contains(strtolower($m->name), 'text') || str_contains(strtolower($m->name), 'shape')) {
        echo "- " . $m->name . "(";
        $params = [];
        foreach ($m->getParameters() as $p) {
            $params[] = '$' . $p->name . ($p->isOptional() ? ' = ' . var_export($p->getDefaultValue(), true) : '');
        }
        echo implode(', ', $params) . ")\n";
    }
}
