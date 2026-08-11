<?php

if (!function_exists('format_sys_date')) {
    function format_sys_date($date, $hasTime = false) {
        if (empty($date)) return 'غ/م';
        try {
            $format = $hasTime ? 'd/m/Y H:i' : 'd/m/Y';
            if ($date instanceof \DateTimeInterface) {
                return $date->format($format);
            }
            return \Carbon\Carbon::parse($date)->format($format);
        } catch (\Throwable $e) {
            return $date;
        }
    }
}
