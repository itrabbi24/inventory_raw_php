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

/**
 * Convert number to words (Basic)
 */
if (!function_exists('numberToWords')) {
    function numberToWords($number) {
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) return false;
        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) return false;

        if ($number < 0) return $negative . numberToWords(abs($number));

        $string = $fraction = null;
        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . numberToWords($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= numberToWords($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}

/**
 * Handle File Uploads (Secure)
 */
if (!function_exists('uploadFile')) {
    function uploadFile($file, $target_dir) {
        if (!isset($file['name']) || empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $target_dir = rtrim($target_dir, '/') . '/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $file_name = preg_replace("/[^a-zA-Z0-9.\-_]/", "", basename($file["name"]));
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid() . '_' . time() . '.' . $file_ext;
        $target_file = $target_dir . $new_file_name;

        // Allow certain file formats
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if(!in_array($file_ext, $allowed)) return false;

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $new_file_name;
        }

        return false;
    }
}


