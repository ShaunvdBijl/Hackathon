<?php
session_start();

// Check if user is authenticated (for user downloads) or admin (for admin downloads)
$isAdmin = isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated'];
$isUser = isset($_SESSION['user_email']);

if (!$isAdmin && !$isUser) {
    http_response_code(401);
    die('Access denied - Authentication required');
}

// Get file parameter
$fileName = $_GET['file'] ?? '';
$userEmail = $_GET['user'] ?? ($_SESSION['user_email'] ?? '');

if (empty($fileName)) {
    http_response_code(400);
    die('File name required');
}

if (empty($userEmail)) {
    http_response_code(400);
    die('User email required');
}

// Set up paths
$uploadDir = __DIR__ . '/../uploads';
$userSafe = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $userEmail);

// For admin access, allow specifying user
if ($isAdmin && isset($_GET['user'])) {
    $filePath = $uploadDir . '/' . $userSafe . '/' . $fileName;
} else {
    // For regular users, only allow access to their own files
    $userSafe = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $_SESSION['user_email']);
    $filePath = $uploadDir . '/' . $userSafe . '/' . $fileName;
}

// Security check - ensure file is within upload directory
$realUploadDir = realpath($uploadDir);
$realFilePath = realpath($filePath);

// Check if directories exist
if (!$realUploadDir) {
    http_response_code(500);
    die('Upload directory not found');
}

if (!$realFilePath) {
    // Check if directory exists
    $dirPath = dirname($filePath);
    if (!is_dir($dirPath)) {
        http_response_code(404);
        die("User directory not found");
    } else {
        http_response_code(404);
        die("File not found");
    }
}

if (strpos($realFilePath, $realUploadDir) !== 0) {
    http_response_code(403);
    die('Access denied - Invalid file path');
}

// Check if file exists
if (!file_exists($filePath)) {
    http_response_code(404);
    die('File not found');
}

// Get file info
$fileSize = filesize($filePath);
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Set appropriate headers
$mimeTypes = [
    'zip' => 'application/zip',
    'rar' => 'application/x-rar-compressed',
    '7z' => 'application/x-7z-compressed'
];

$mimeType = $mimeTypes[$fileExtension] ?? 'application/octet-stream';

header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Output file
readfile($filePath);
exit;
?>
