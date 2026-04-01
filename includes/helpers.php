<?php
/**
 * Global Helper Functions
 * General formatting and sanitization utilities.
 */

/**
 * Sanitize input to prevent XSS
 */
if (!function_exists('sanitize')) {
    function sanitize($data) {
        if ($data === null) return '';
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }
}

/**
 * Format currency with symbol
 */
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $symbol = '৳') {
        return $symbol . ' ' . number_format((float)$amount, 2);
    }
}

/**
 * Generate unique Invoice/Voucher number
 */
if (!function_exists('generateInvoiceNo')) {
    function generateInvoiceNo($pdo, $prefix, $table, $column) {
        $year = date('y');
        $month = date('m');
        $query = "SELECT $column FROM $table WHERE $column LIKE '$prefix-$year$month%' ORDER BY id DESC LIMIT 1";
        $stmt = $pdo->query($query);
        $last = $stmt->fetchColumn();

        if ($last) {
            $num = (int)substr($last, -4);
            $newNum = str_pad($num + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNum = '0001';
        }

        return "$prefix-$year$month$newNum";
    }
}
