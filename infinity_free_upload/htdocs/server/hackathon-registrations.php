<?php
session_start();
header('Content-Type: application/json');

// Production CORS - allow all origins for now (update with your domain later)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
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

function getRegistrationsFile() {
    $dataDir = '../data_store';
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }
    return $dataDir . '/hackathon-registrations.json';
}

function loadRegistrations() {
    $file = getRegistrationsFile();
    if (file_exists($file)) {
        $data = file_get_contents($file);
        return json_decode($data, true) ?: [];
    }
    return [];
}

function saveRegistrations($registrations) {
    $file = getRegistrationsFile();
    file_put_contents($file, json_encode($registrations, JSON_PRETTY_PRINT));
}

function logAction($action, $data) {
    $logFile = '../data_store/hackathon-registrations.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $action: " . json_encode($data) . "\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        respond(['error' => 'Invalid JSON input'], 400);
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'register':
            handleRegistration($input);
            break;
            
        case 'update':
            handleUpdate($input);
            break;
            
        case 'get_user_registrations':
            handleGetUserRegistrations($input);
            break;
            
        default:
            respond(['error' => 'Invalid action'], 400);
    }
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'get_user_registrations':
            handleGetUserRegistrations($_GET);
            break;
            
        case 'get_hackathon_registrations':
            handleGetHackathonRegistrations($_GET);
            break;
            
        default:
            respond(['error' => 'Invalid action'], 400);
    }
}

function handleRegistration($input) {
    $registration = $input['registration'] ?? null;
    
    if (!$registration) {
        respond(['error' => 'Registration data required'], 400);
    }
    
    // Validate required fields
    $required = ['hackathonId', 'userEmail'];
    foreach ($required as $field) {
        if (empty($registration[$field])) {
            respond(['error' => "Missing required field: $field"], 400);
        }
    }
    
    // Load existing registrations
    $registrations = loadRegistrations();
    
    // Check if user is already registered
    if (isset($registrations[$registration['hackathonId']])) {
        foreach ($registrations[$registration['hackathonId']] as $existingReg) {
            if ($existingReg['userEmail'] === $registration['userEmail']) {
                respond(['error' => 'User already registered for this hackathon'], 409);
            }
        }
    }
    
    // Add registration
    if (!isset($registrations[$registration['hackathonId']])) {
        $registrations[$registration['hackathonId']] = [];
    }
    
    $registrations[$registration['hackathonId']][] = $registration;
    
    // Save to file
    saveRegistrations($registrations);
    
    // Log the action
    logAction('REGISTER', $registration);
    
    respond(['ok' => true, 'message' => 'Registration successful', 'registration' => $registration]);
}

function handleUpdate($input) {
    $hackathonId = $input['hackathonId'] ?? '';
    $userEmail = $input['userEmail'] ?? '';
    $status = $input['status'] ?? '';
    
    if (!$hackathonId || !$userEmail) {
        respond(['error' => 'Hackathon ID and user email required'], 400);
    }
    
    $registrations = loadRegistrations();
    
    if (!isset($registrations[$hackathonId])) {
        respond(['error' => 'Hackathon not found'], 404);
    }
    
    // Find and update the registration
    $found = false;
    foreach ($registrations[$hackathonId] as &$reg) {
        if ($reg['userEmail'] === $userEmail) {
            $reg['status'] = $status;
            $reg['updatedAt'] = date('Y-m-d H:i:s');
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        respond(['error' => 'Registration not found'], 404);
    }
    
    // Save updated registrations
    saveRegistrations($registrations);
    
    // Log the action
    logAction('UPDATE', ['hackathonId' => $hackathonId, 'userEmail' => $userEmail, 'status' => $status]);
    
    respond(['ok' => true, 'message' => 'Registration updated successfully']);
}

function handleGetUserRegistrations($input) {
    $userEmail = $input['user_email'] ?? $input['userEmail'] ?? '';
    
    if (!$userEmail) {
        respond(['error' => 'User email required'], 400);
    }
    
    $registrations = loadRegistrations();
    $userRegistrations = [];
    
    foreach ($registrations as $hackathonId => $hackathonRegs) {
        foreach ($hackathonRegs as $reg) {
            if ($reg['userEmail'] === $userEmail) {
                $userRegistrations[] = array_merge($reg, ['hackathonId' => $hackathonId]);
            }
        }
    }
    
    respond(['ok' => true, 'registrations' => $userRegistrations]);
}

function handleGetHackathonRegistrations($input) {
    $hackathonId = $input['hackathon_id'] ?? $input['hackathonId'] ?? '';
    
    if (!$hackathonId) {
        respond(['error' => 'Hackathon ID required'], 400);
    }
    
    $registrations = loadRegistrations();
    $hackathonRegistrations = $registrations[$hackathonId] ?? [];
    
    respond(['ok' => true, 'registrations' => $hackathonRegistrations]);
}

// Default response for unsupported methods
respond(['error' => 'Method not allowed'], 405);
?>
