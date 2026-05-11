<?php
// ============================================================
//  auth.php — Register / Login / Logout / Me
//  Now reads/writes the MySQL `users` table via db.php
// ============================================================
session_start();
header('Content-Type: application/json');

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

require_once __DIR__ . '/db.php';

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// ── REGISTER ────────────────────────────────────────────────
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $required = ['firstName', 'lastName', 'email', 'studentId', 'password'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            respond(['error' => "Missing required field: $field"], 400);
        }
    }

    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        respond(['error' => 'Invalid email format'], 400);
    }
    if (strlen($input['password']) < 8) {
        respond(['error' => 'Password must be at least 8 characters long'], 400);
    }
    if (!preg_match('/\d/', $input['password'])) {
        respond(['error' => 'Password must contain at least 1 number'], 400);
    }
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $input['password'])) {
        respond(['error' => 'Password must contain at least 1 special character'], 400);
    }
    if (strlen($input['studentId']) !== 6) {
        respond(['error' => 'Student ID must be exactly 6 characters long'], 400);
    }

    $db = getDB();

    // Check for duplicates
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? OR student_id = ?');
    $stmt->execute([trim($input['email']), trim($input['studentId'])]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Determine which field conflicts
        $emailStmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $emailStmt->execute([trim($input['email'])]);
        if ($emailStmt->fetch()) {
            respond(['error' => 'Email already registered'], 409);
        }
        respond(['error' => 'Student ID already registered'], 409);
    }

    $newId = uniqid();
    $hash  = password_hash($input['password'], PASSWORD_DEFAULT);

    $stmt = $db->prepare(
        'INSERT INTO users (id, first_name, last_name, email, student_id, password_hash, role, created_at)
         VALUES (?, ?, ?, ?, ?, ?, \'student\', NOW())'
    );
    $stmt->execute([
        $newId,
        trim($input['firstName']),
        trim($input['lastName']),
        trim($input['email']),
        trim($input['studentId']),
        $hash
    ]);

    respond(['ok' => true, 'message' => 'User registered successfully']);
}

// ── LOGIN ────────────────────────────────────────────────────
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input    = json_decode(file_get_contents('php://input'), true);
    $email    = isset($input['email'])    ? trim($input['email'])    : '';
    $password = isset($input['password']) ? $input['password']       : '';

    $db   = getDB();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        respond(['error' => 'Email address not found in our system'], 401);
    }

    if (!password_verify($password, $user['password_hash'])) {
        respond(['error' => 'Password is incorrect for this email address'], 401);
    }

    $_SESSION['user_email']      = $user['email'];
    $_SESSION['user_id']         = $user['id'];
    $_SESSION['user_name']       = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_type']       = 'registered';
    $_SESSION['user_student_id'] = $user['student_id'];
    $_SESSION['user_created_at'] = $user['created_at'];

    respond([
        'ok'        => true,
        'email'     => $user['email'],
        'name'      => $user['first_name'] . ' ' . $user['last_name'],
        'type'      => 'registered',
        'studentId' => $user['student_id'],
        'createdAt' => $user['created_at']
    ]);
}

// ── LOGOUT ───────────────────────────────────────────────────
if ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();
    respond(['ok' => true]);
}

// ── ME (session check) ───────────────────────────────────────
if ($action === 'me') {
    if (isset($_SESSION['user_email'])) {
        respond([
            'ok'        => true,
            'email'     => $_SESSION['user_email'],
            'name'      => $_SESSION['user_name']       ?? 'Demo User',
            'type'      => $_SESSION['user_type']       ?? 'demo',
            'studentId' => $_SESSION['user_student_id'] ?? 'Not available',
            'createdAt' => $_SESSION['user_created_at'] ?? null
        ]);
    } else {
        respond(['ok' => false], 401);
    }
}

respond(['error' => 'Invalid action'], 400);
?>
