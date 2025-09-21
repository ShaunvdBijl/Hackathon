<?php
session_start();
header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['user_email'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated']);
    exit;
}

// Set up upload directory
$uploadDir = __DIR__ . '/../uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Create user-specific directory
$userSafe = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $_SESSION['user_email']);
$userUploadDir = $uploadDir . '/' . $userSafe;
if (!is_dir($userUploadDir)) {
    mkdir($userUploadDir, 0755, true);
}

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle file upload
    if (!isset($_FILES['projectFile'])) {
        respond(['ok' => false, 'error' => 'No file uploaded'], 400);
    }
    
    $file = $_FILES['projectFile'];
    
    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        respond(['ok' => false, 'error' => 'File upload error: ' . $file['error']], 400);
    }
    
    // Check file size (50MB limit)
    $maxSize = 50 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        respond(['ok' => false, 'error' => 'File too large. Maximum size: 50MB'], 400);
    }
    
    // Check file type
    $allowedTypes = ['zip', 'rar', '7z'];
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($fileExtension, $allowedTypes)) {
        respond(['ok' => false, 'error' => 'Invalid file type. Allowed: ZIP, RAR, 7Z'], 400);
    }
    
    // Generate unique filename with timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $uniqueId = uniqid();
    $safeFileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
    $newFileName = $timestamp . '_' . $uniqueId . '_' . $safeFileName . '.' . $fileExtension;
    $targetPath = $userUploadDir . '/' . $newFileName;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // File uploaded successfully
        $fileInfo = [
            'original_name' => $file['name'],
            'stored_name' => $newFileName,
            'file_path' => 'uploads/' . $userSafe . '/' . $newFileName,
            'file_size' => $file['size'],
            'file_type' => $fileExtension,
            'upload_time' => date('c'),
            'user_email' => $_SESSION['user_email']
        ];
        
        respond([
            'ok' => true, 
            'message' => 'File uploaded successfully',
            'file_info' => $fileInfo
        ]);
    } else {
        respond(['ok' => false, 'error' => 'Failed to save file'], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // List user's uploaded files
    $files = [];
    if (is_dir($userUploadDir)) {
        $fileList = scandir($userUploadDir);
        foreach ($fileList as $fileName) {
            if ($fileName !== '.' && $fileName !== '..') {
                $filePath = $userUploadDir . '/' . $fileName;
                $files[] = [
                    'name' => $fileName,
                    'original_name' => $fileName, // Could be enhanced to store original names
                    'size' => filesize($filePath),
                    'modified' => date('c', filemtime($filePath)),
                    'download_url' => 'server/download.php?file=' . urlencode($fileName)
                ];
            }
        }
    }
    
    respond(['ok' => true, 'files' => $files]);
}

respond(['ok' => false, 'error' => 'Method not allowed'], 405);
?>
