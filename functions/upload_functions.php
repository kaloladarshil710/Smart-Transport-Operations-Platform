<?php
/**
 * Upload helpers for TransitOps.
 *
 * Handles:
 * - Vehicle document upload (PDF/JPG/PNG, max 5MB)
 * - Vehicle image upload with resize/compress + thumbnail using GD
 * - Secure filename generation
 *
 * @package TransitOps
 */

declare(strict_types=1);

/**
 * Validate uploaded file and return sanitized metadata.
 *
 * @param array<string, mixed> $file $_FILES['...'] entry
 * @param array<int, string> $allowedExt
 * @param int $maxBytes
 * @return array{ext:string, size:int, tmp_name:string, mime:string, original_name:string}
 */
function validateUploadedFile(array $file, array $allowedExt, int $maxBytes = 5242880): array
{
    if (empty($file) || empty($file['tmp_name']) || !is_uploaded_file((string)$file['tmp_name'])) {
        throw new InvalidArgumentException('No file uploaded.');
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > $maxBytes) {
        throw new InvalidArgumentException('File size must be <= 5MB.');
    }

    $originalName = (string)($file['name'] ?? 'upload');
    $ext = strtolower((string)pathinfo($originalName, PATHINFO_EXTENSION));

    if ($ext === '' || !in_array($ext, $allowedExt, true)) {
        throw new InvalidArgumentException('Invalid file type.');
    }

    $mime = (string)($file['type'] ?? 'application/octet-stream');

    return [
        'ext' => $ext,
        'size' => $size,
        'tmp_name' => (string)$file['tmp_name'],
        'mime' => $mime,
        'original_name' => $originalName,
    ];
}

/**
 * Generate a safe storage filename.
 *
 * @param string $ext
 * @param string $prefix
 * @return string
 */
function generateUploadFileName(string $ext, string $prefix = 'file'): string
{
    return $prefix . '_' . bin2hex(random_bytes(16)) . '.' . strtolower($ext);
}

/**
 * Upload a document file to a destination folder.
 *
 * @param int $vehicleId
 * @param array<string, mixed> $file
 * @param string $documentType
 * @return string Relative file path stored under uploads/
 */
function uploadVehicleDocument(int $vehicleId, array $file, string $documentType): string
{
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
    $meta = validateUploadedFile($file, $allowed);

    $documentType = trim($documentType);
    if ($documentType === '') {
        throw new InvalidArgumentException('Document type is required.');
    }

    $baseDir = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'vehicle_documents';
    if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true)) {
        throw new RuntimeException('Failed to create upload directory.');
    }

    $fileName = generateUploadFileName($meta['ext'], 'veh_' . $vehicleId . '_' . strtolower($documentType));
    $destination = $baseDir . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($meta['tmp_name'], $destination)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    return 'uploads/vehicle_documents/' . $fileName;
}

/**
 * Upload vehicle profile photo and generate a thumbnail.
 *
 * @param int $vehicleId
 * @param array<string, mixed> $file
 * @return string Relative original photo path
 */
function uploadVehiclePhoto(int $vehicleId, array $file): string
{
    $allowed = ['jpg', 'jpeg', 'png'];
    $meta = validateUploadedFile($file, $allowed);

    $baseDir = UPLOAD_PATH . DIRECTORY_SEPARATOR . 'vehicle' . DIRECTORY_SEPARATOR . $vehicleId;
    $thumbDir = $baseDir . DIRECTORY_SEPARATOR . 'thumbs';

    if (!is_dir($baseDir) && !mkdir($baseDir, 0775, true)) {
        throw new RuntimeException('Failed to create photo directory.');
    }
    if (!is_dir($thumbDir) && !mkdir($thumbDir, 0775, true)) {
        throw new RuntimeException('Failed to create thumb directory.');
    }

    $ext = $meta['ext'];
    $fileName = generateUploadFileName($ext, 'photo');
    $destination = $baseDir . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($meta['tmp_name'], $destination)) {
        throw new RuntimeException('Failed to move uploaded photo.');
    }

    $thumbName = pathinfo($fileName, PATHINFO_FILENAME) . '_thumb.' . $ext;
    $thumbPath = $thumbDir . DIRECTORY_SEPARATOR . $thumbName;

    // Resize/compress original
    $sourceImg = loadImageResource($destination, $ext);
    $sourceW = imagesx($sourceImg);
    $sourceH = imagesy($sourceImg);

    $maxW = 900;
    $maxH = 600;

    $scale = min($maxW / $sourceW, $maxH / $sourceH, 1);
    $newW = (int)round($sourceW * $scale);
    $newH = (int)round($sourceH * $scale);

    $resized = imagecreatetruecolor($newW, $newH);
    imagecopyresampled($resized, $sourceImg, 0, 0, 0, 0, $newW, $newH, $sourceW, $sourceH);
    saveImageResource($resized, $destination, $ext, 80);

    imagedestroy($resized);
    imagedestroy($sourceImg);

    // Thumbnail
    $sourceImg2 = loadImageResource($destination, $ext);
    $sourceW2 = imagesx($sourceImg2);
    $sourceH2 = imagesy($sourceImg2);

    $thumbMax = 220;
    $scaleT = min($thumbMax / $sourceW2, $thumbMax / $sourceH2, 1);
    $tW = (int)round($sourceW2 * $scaleT);
    $tH = (int)round($sourceH2 * $scaleT);

    $thumbResized = imagecreatetruecolor($tW, $tH);
    imagecopyresampled($thumbResized, $sourceImg2, 0, 0, 0, 0, $tW, $tH, $sourceW2, $sourceH2);
    saveImageResource($thumbResized, $thumbPath, $ext, 70);

    imagedestroy($thumbResized);
    imagedestroy($sourceImg2);

    return 'uploads/vehicle/' . $vehicleId . '/' . $fileName;
}

/**
 * Load image resource from file.
 *
 * @param string $path
 * @param string $ext
 * @return GdImage
 */
function loadImageResource(string $path, string $ext)
{
    $ext = strtolower($ext);
    if ($ext === 'png') {
        return imagecreatefrompng($path);
    }

    return imagecreatefromjpeg($path);
}

/**
 * Save GD image resource to file.
 *
 * @param GdImage $img
 * @param string $path
 * @param string $ext
 * @param int $quality
 * @return void
 */
function saveImageResource($img, string $path, string $ext, int $quality = 80): void
{
    $ext = strtolower($ext);
    if ($ext === 'png') {
        // Map JPEG quality-ish (0-100) to PNG compression (0-9).
        $compression = max(0, min(9, (int)round((9 - $quality / 10))));
        imagepng($img, $path, $compression);
        return;
    }

    imagejpeg($img, $path, $quality);
}

