<?php
// ============================================================
//  submissions.php — Create / List / Delete submissions
//  Now reads/writes the MySQL `submissions` + `submission_files` tables
// ============================================================
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Get user email from session or request
$userEmail = null;
if (isset($_SESSION['user_email'])) {
    $userEmail = $_SESSION['user_email'];
} elseif (isset($_GET['user_email'])) {
    $userEmail = $_GET['user_email'];
} elseif (isset($_POST['user_email'])) {
    $userEmail = $_POST['user_email'];
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

// ── GET — list user's submissions ────────────────────────────
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    $stmt = $db->prepare(
        'SELECT s.*, GROUP_CONCAT(
            JSON_OBJECT(
                "id",            sf.id,
                "original_name", sf.original_name,
                "stored_name",   sf.stored_name,
                "file_path",     sf.file_path,
                "file_size",     sf.file_size,
                "file_type",     sf.file_type
            )
        ) AS attached_files_json
        FROM submissions s
        LEFT JOIN submission_files sf ON sf.submission_id = s.id
        WHERE s.user_email = ?
        GROUP BY s.id
        ORDER BY s.created_at DESC'
    );
    $stmt->execute([$userEmail]);
    $rows = $stmt->fetchAll();

    // Parse attached files from GROUP_CONCAT
    $submissions = [];
    foreach ($rows as $row) {
        $files = [];
        if (!empty($row['attached_files_json'])) {
            // GROUP_CONCAT returns comma-separated JSON objects
            $jsonParts = explode('},{', $row['attached_files_json']);
            foreach ($jsonParts as $i => $part) {
                // Fix braces that got split
                if ($i > 0) $part = '{' . $part;
                if ($i < count($jsonParts) - 1) $part = $part . '}';
                $decoded = json_decode($part, true);
                if ($decoded && $decoded['id'] !== null) {
                    $files[] = $decoded;
                }
            }
        }
        unset($row['attached_files_json']);

        $row['attached_files'] = $files;

        // Map DB column names back to the keys the frontend expects
        $submissions[] = [
            'id'             => (int)$row['id'],
            'title'          => $row['title'],
            'desc'           => $row['description'],
            'link'           => $row['project_link'],
            'category'       => $row['category'],
            'user_email'     => $row['user_email'],
            'createdAt'      => $row['created_at'],
            'ip_address'     => $row['ip_address'],
            'attached_files' => $files
        ];
    }

    if ($action === 'user_submissions') {
        echo json_encode(['ok' => true, 'submissions' => $submissions]);
    } else {
        echo json_encode(['ok' => true, 'items' => $submissions]);
    }
    exit;
}

// ── POST — create a new submission ───────────────────────────
if ($method === 'POST') {
    $input    = json_decode(file_get_contents('php://input'), true);
    $title    = isset($input['title'])    ? trim($input['title'])    : '';
    $desc     = isset($input['desc'])     ? trim($input['desc'])     : '';
    $link     = isset($input['link'])     ? trim($input['link'])     : '';
    $category = isset($input['category']) ? trim($input['category']) : '';

    if ($title === '' || $desc === '' || $category === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Missing required fields: title, description, and category']);
        exit;
    }

    $attachedFiles = [];
    if (isset($input['files']) && is_array($input['files'])) {
        $attachedFiles = $input['files'];
    }

    $hasFiles = !empty($attachedFiles);
    $hasLink  = !empty($link);

    if (!$hasFiles && !$hasLink) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Please provide either a project link or upload project files']);
        exit;
    }

    $submissionId = round(microtime(true) * 1000);
    $ipAddress    = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    $stmt = $db->prepare(
        'INSERT INTO submissions (id, user_email, title, description, project_link, category, ip_address, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $submissionId,
        $userEmail,
        $title,
        $desc,
        $link ?: null,
        $category,
        $ipAddress
    ]);

    // Insert attached file records
    if ($hasFiles) {
        $fileStmt = $db->prepare(
            'INSERT INTO submission_files (submission_id, original_name, stored_name, file_path, file_size, file_type, uploaded_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        foreach ($attachedFiles as $f) {
            $fileStmt->execute([
                $submissionId,
                $f['original_name'] ?? $f['name'] ?? 'unknown',
                $f['stored_name']   ?? $f['name'] ?? 'unknown',
                $f['file_path']     ?? '',
                $f['file_size']     ?? $f['size'] ?? 0,
                $f['file_type']     ?? $f['type'] ?? 'zip'
            ]);
        }
    }

    echo json_encode(['ok' => true, 'message' => 'Submission saved successfully']);
    exit;
}

// ── DELETE — remove a submission ─────────────────────────────
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    // Ensure user can only delete their own submissions
    $stmt = $db->prepare('DELETE FROM submissions WHERE id = ? AND user_email = ?');
    $stmt->execute([$id, $userEmail]);

    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
?>
