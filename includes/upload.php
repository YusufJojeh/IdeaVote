<?php
// Secure image upload utility (procedural)

require_once __DIR__ . '/functions.php';

if (!function_exists('upload_image')) {
    /**
     * @param array $file The $_FILES[...] entry
     * @param string $subdir 'avatars' | 'ideas'
     * @param int $maxBytes Default 2MB
     * @return array ['ok'=>bool,'path'=>string,'error'=>?string,'width'=>int,'height'=>int]
     */
    function upload_image(array $file, string $subdir, int $maxBytes = 2097152): array {
        $result = ['ok' => false, 'path' => '', 'error' => null, 'width' => 0, 'height' => 0];

        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $result['error'] = 'upload_failed';
            return $result;
        }
        if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) {
            $result['error'] = 'upload_too_large';
            return $result;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($allowed[$mime])) {
            $result['error'] = 'upload_invalid_mime';
            return $result;
        }

        // Ensure uploads directory exists and is protected
        $baseDir = __DIR__ . '/../uploads';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        // Create subdir
        $targetDir = $baseDir . '/' . basename($subdir);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Re-encode using GD to strip any malicious payloads
        $image = null;
        if ($mime === 'image/jpeg') {
            $image = @imagecreatefromjpeg($file['tmp_name']);
        } elseif ($mime === 'image/png') {
            $image = @imagecreatefrompng($file['tmp_name']);
        } elseif ($mime === 'image/webp') {
            $image = @imagecreatefromwebp($file['tmp_name']);
        }
        if (!$image) {
            $result['error'] = 'upload_failed';
            return $result;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $result['width'] = $width;
        $result['height'] = $height;

        // Determine extension/encoding: keep PNG if source had alpha
        $ext = $allowed[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetPathAbs = $targetDir . '/' . $filename;

        $ok = false;
        if ($mime === 'image/png') {
            imagesavealpha($image, true);
            $ok = imagepng($image, $targetPathAbs, 6);
        } elseif ($mime === 'image/jpeg') {
            $ok = imagejpeg($image, $targetPathAbs, 85);
        } elseif ($mime === 'image/webp') {
            $ok = imagewebp($image, $targetPathAbs, 85);
        }
        imagedestroy($image);

        if (!$ok) {
            $result['error'] = 'upload_failed';
            return $result;
        }

        // Return web-relative path
        $result['ok'] = true;
        $result['path'] = 'uploads/' . basename($subdir) . '/' . $filename;
        return $result;
    }
}

?>


