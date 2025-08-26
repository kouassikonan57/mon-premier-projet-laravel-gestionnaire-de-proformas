<?php
if (!function_exists('format_fcfa')) {
    function format_fcfa($amount)
    {
        return number_format($amount, 0, ',', ' ') . ' F CFA';
    }
}
