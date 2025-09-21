<?php
session_start();
header('Content-Type: application/json');

// Get user email from session or request
$userEmail = null;
if (isset($_SESSION['user_email'])) {
    $userEmail = $_SESSION['user_email'];
} elseif (isset($_GET['user_email'])) {
    $userEmail = $_GET['user_email'];
} elseif (isset($_POST['user_email'])) {
    $userEmail = $_POST['user_email'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Try to get from JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['user_email'])) {
        $userEmail = $input['user_email'];
    }
}

if (!$userEmail) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not authenticated - no user email provided']);
    exit;
}

// Simple file-backed storage per user (for demo; not for production)
$dataDir = __DIR__ . '/../data_store';
if (!is_dir($dataDir)) { mkdir($dataDir, 0777, true); }
$userSafe = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $userEmail);
$userFile = $dataDir . '/' . $userSafe . '.json';

function load_user_list($file) {
    if (!file_exists($file)) return [];
    $raw = file_get_contents($file);
    $json = json_decode($raw, true);
    return is_array($json) ? $json : [];
}

function save_user_list($file, $list) {
    file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'user_submissions') {
        // Return user's submissions for dashboard
        echo json_encode(['ok' => true, 'submissions' => load_user_list($userFile)]);
        exit;
    }
    
    // Default: return all user's items
    echo json_encode(['ok' => true, 'items' => load_user_list($userFile)]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $title = isset($input['title']) ? trim($input['title']) : '';
    $desc = isset($input['desc']) ? trim($input['desc']) : '';
    $link = isset($input['link']) ? trim($input['link']) : '';
    $category = isset($input['category']) ? trim($input['category']) : '';
    
    if ($title === '' || $desc === '' || $category === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing required fields: title, description, and category']);
        exit;
    }
    
    // Handle file attachments
    $attachedFiles = [];
    if (isset($input['files']) && is_array($input['files'])) {
        $attachedFiles = $input['files'];
    }
    
    // Check that either link OR files are provided
    $hasFiles = !empty($attachedFiles);
    $hasLink = !empty($link);
    
    if (!$hasFiles && !$hasLink) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Please provide either a project link or upload project files']);
        exit;
    }
    
    $submission = [
        'id' => round(microtime(true) * 1000),
        'title' => $title,
        'desc' => $desc,
        'link' => $link,
        'category' => $category,
        'user_email' => $userEmail,
        'createdAt' => date('c'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'attached_files' => $attachedFiles
    ];
    
    // Store in user's personal file
    $list = load_user_list($userFile);
    $list[] = $submission;
    save_user_list($userFile, $list);
    
    // Also store in central submissions file for admin
    $centralFile = $dataDir . '/all_submissions.json';
    $allSubmissions = [];
    if (file_exists($centralFile)) {
        $allSubmissions = json_decode(file_get_contents($centralFile), true) ?: [];
    }
    $allSubmissions[] = $submission;
    file_put_contents($centralFile, json_encode($allSubmissions, JSON_PRETTY_PRINT));
    
    echo json_encode(['ok' => true, 'message' => 'Submission saved successfully']);
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $list = load_user_list($userFile);
    $list = array_values(array_filter($list, function($x) use ($id) { return intval($x['id']) !== $id; }));
    save_user_list($userFile, $list);
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
?>


