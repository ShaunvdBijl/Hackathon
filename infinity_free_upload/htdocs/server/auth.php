<?php
session_start();
header('Content-Type: application/json');

// Allow simple CORS for local testing (same-origin recommended in production)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
    header('Access-Control-Allow-Credentials', 'true');
    header('Vary', 'Origin');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

function respond($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getUserDataFile() {
    $dataDir = '../data_store';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    return $dataDir . '/users.json';
}

function loadUsers() {
    $file = getUserDataFile();
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function saveUsers($users) {
    $file = getUserDataFile();
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['firstName', 'lastName', 'email', 'studentId', 'password'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            respond(['error' => "Missing required field: $field"], 400);
        }
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        respond(['error' => 'Invalid email format'], 400);
    }
    
    // Validate password requirements: 8 characters, 1 number, 1 special character
    if (strlen($input['password']) < 8) {
        respond(['error' => 'Password must be at least 8 characters long'], 400);
    }
    
    if (!preg_match('/\d/', $input['password'])) {
        respond(['error' => 'Password must contain at least 1 number'], 400);
    }
    
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $input['password'])) {
        respond(['error' => 'Password must contain at least 1 special character'], 400);
    }
    
    // Validate student ID: exactly 6 characters
    if (strlen($input['studentId']) !== 6) {
        respond(['error' => 'Student ID must be exactly 6 characters long'], 400);
    }
    
    $users = loadUsers();
    
    // Check if email already exists
    foreach ($users as $user) {
        if ($user['email'] === $input['email']) {
            respond(['error' => 'Email already registered'], 409);
        }
        if ($user['studentId'] === $input['studentId']) {
            respond(['error' => 'Student ID already registered'], 409);
        }
    }
    
    // Create new user
    $newUser = [
        'id' => uniqid(),
        'firstName' => trim($input['firstName']),
        'lastName' => trim($input['lastName']),
        'email' => trim($input['email']),
        'studentId' => trim($input['studentId']),
        'password' => hashPassword($input['password']),
        'createdAt' => date('Y-m-d H:i:s')
    ];
    
    $users[] = $newUser;
    saveUsers($users);
    
    respond(['ok' => true, 'message' => 'User registered successfully']);
}

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? $input['password'] : '';

    // Check registered users
    $users = loadUsers();
    
    $emailFound = false;
    $passwordValid = false;
    
    foreach ($users as $user) {
        if ($user['email'] === $email) {
            $emailFound = true;
            
            $passwordValid = verifyPassword($password, $user['password']);
            
            if ($passwordValid) {
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
                $_SESSION['user_type'] = 'registered';
                $_SESSION['user_student_id'] = $user['studentId'];
                $_SESSION['user_created_at'] = $user['createdAt'];
                respond([
                    'ok' => true,
                    'email' => $user['email'],
                    'name' => $user['firstName'] . ' ' . $user['lastName'],
                    'type' => 'registered',
                    'studentId' => $user['studentId'],
                    'createdAt' => $user['createdAt']
                ]);
            }
            break; // Found the email, no need to check other users
        }
    }
    
    // Provide specific error messages
    if (!$emailFound) {
        respond(['error' => 'Email address not found in our system'], 401);
    } else if (!$passwordValid) {
        respond(['error' => 'Password is incorrect for this email address'], 401);
    } else {
        respond(['error' => 'Authentication failed'], 401);
    }
}

if ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_unset();
    session_destroy();
    respond(['ok' => true]);
}

if ($action === 'me') {
    if (isset($_SESSION['user_email'])) {
        respond([
            'ok' => true,
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'] ?? 'Demo User',
            'type' => $_SESSION['user_type'] ?? 'demo',
            'studentId' => $_SESSION['user_student_id'] ?? 'Not available',
            'createdAt' => $_SESSION['user_created_at'] ?? null
        ]);
    } else {
        respond(['ok' => false], 401);
    }
}








