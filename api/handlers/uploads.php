<?php
// handlers/uploads.php
// Simple file upload endpoint for portfolio files
// POST /api.php?resource=uploads  (multipart form-data with field name "file")
function handleUploadsResource($pdo, $method, $id, $input) {
    if ($method !== 'POST') {
        respond(['error' => 'Method not allowed'], 405);
    }

    if (!isset($_FILES) || empty($_FILES) || !isset($_FILES['file'])) {
        respond(['error' => 'Missing file (field name: file)'], 400);
    }

    $file = $_FILES['file'];
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        respond(['error' => 'Upload error', 'code' => ($file['error'] ?? 'unknown')], 400);
    }

    // Basic validation
    $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif',
        'application/pdf', 'video/mp4', 'video/quicktime'
    ];
    $maxSize = 10 * 1024 * 1024; // 10 MB

    $fType = $file['type'] ?? '';
    if (!in_array($fType, $allowedTypes)) {
        // allow if extension looks safe (fallback)
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExt = ['jpg','jpeg','png','gif','pdf','mp4','mov'];
        if (!in_array($ext, $allowedExt)) {
            respond(['error' => 'Unsupported file type'], 400);
        }
    }

    if ($file['size'] > $maxSize) {
        respond(['error' => 'File too large, max 10MB'], 400);
    }

    // Destination folder (web-accessible path: /jasaku_api/uploads/portfolios/)
    $destDir = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'portfolios' . DIRECTORY_SEPARATOR;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $origName = $file['name'];
    $ext = pathinfo($origName, PATHINFO_EXTENSION);
    $safeExt = preg_replace('/[^a-zA-Z0-9]/', '', $ext);
    $safeName = bin2hex(random_bytes(8)) . ($safeExt ? '.' . $safeExt : '');
    $target = $destDir . $safeName;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        respond(['error' => 'Failed to move uploaded file'], 500);
    }

    // Build public URL (adjust if you host under a different base path)
    // Using absolute path relative to htdocs: /jasaku_api/uploads/portfolios/<file>
    $publicUrl = '/jasaku_api/uploads/portfolios/' . $safeName;

    respond(['url' => $publicUrl, 'filename' => $safeName]);
}

?>