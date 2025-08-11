<?php
/**
 * Internationalization helper
 * Loads language files and provides translation functions
 */

// Load language files
$lang = $_SESSION['language'] ?? 'en';
$langFile = __DIR__ . "/../assets/lang/{$lang}.php";

if (file_exists($langFile)) {
    $translations = include $langFile;
} else {
    // Fallback to English
    $translations = include __DIR__ . "/../assets/lang/en.php";
}

/**
 * Translate a key to the current language
 * 
 * @param string $key Translation key
 * @param array $params Parameters to replace in the translation
 * @return string Translated text
 */
function __($key, $params = []) {
    global $translations;
    
    $text = $translations[$key] ?? $key;
    
    if (!empty($params)) {
        foreach ($params as $param => $value) {
            $text = str_replace("{{$param}}", $value, $text);
        }
    }
    
    return $text;
}

/**
 * Get current language
 * 
 * @return string Language code (en/ar)
 */
function current_language() {
    return $_SESSION['language'] ?? 'en';
}

/**
 * Check if current language is RTL
 * 
 * @return bool True if RTL language
 */
function is_rtl() {
    return current_language() === 'ar';
}

/**
 * Get language direction attribute
 * 
 * @return string 'ltr' or 'rtl'
 */
function lang_dir() {
    return is_rtl() ? 'rtl' : 'ltr';
}

/**
 * Format date according to current language
 * 
 * @param string $date Date string
 * @param string $format Date format
 * @return string Formatted date
 */
function format_date($date, $format = 'M j, Y') {
    if (is_rtl()) {
        // Arabic date formatting
        $timestamp = strtotime($date);
        $months = [
            'Jan' => 'يناير', 'Feb' => 'فبراير', 'Mar' => 'مارس',
            'Apr' => 'أبريل', 'May' => 'مايو', 'Jun' => 'يونيو',
            'Jul' => 'يوليو', 'Aug' => 'أغسطس', 'Sep' => 'سبتمبر',
            'Oct' => 'أكتوبر', 'Nov' => 'نوفمبر', 'Dec' => 'ديسمبر'
        ];
        
        $formatted = date($format, $timestamp);
        foreach ($months as $en => $ar) {
            $formatted = str_replace($en, $ar, $formatted);
        }
        return $formatted;
    }
    
    return date($format, strtotime($date));
}

/**
 * Format number according to current language
 * 
 * @param int $number Number to format
 * @return string Formatted number
 */
function format_number($number) {
    if (is_rtl()) {
        // Arabic numerals
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($english, $arabic, number_format($number));
    }
    
    return number_format($number);
}
