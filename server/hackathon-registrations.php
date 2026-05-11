<?php
// ============================================================
//  hackathon-registrations.php — Register / Unregister / Query
//  Now reads/writes the MySQL `hackathon_registrations` table
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

$db = getDB();

// ── POST requests ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        respond(['error' => 'Invalid JSON input'], 400);
    }

    $action = $input['action'] ?? '';

    switch ($action) {
        case 'register':
            handleRegistration($input, $db);
            break;

        case 'update':
            handleUpdate($input, $db);
            break;

        case 'get_user_registrations':
            handleGetUserRegistrations($input, $db);
            break;

        default:
            respond(['error' => 'Invalid action'], 400);
    }
}

// ── GET requests ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'get_user_registrations':
            handleGetUserRegistrations($_GET, $db);
            break;

        case 'get_hackathon_registrations':
            handleGetHackathonRegistrations($_GET, $db);
            break;

        default:
            respond(['error' => 'Invalid action'], 400);
    }
}

// ── Handlers ─────────────────────────────────────────────────

function handleRegistration($input, $db) {
    $registration = $input['registration'] ?? null;

    if (!$registration) {
        respond(['error' => 'Registration data required'], 400);
    }

    $required = ['hackathonId', 'userEmail'];
    foreach ($required as $field) {
        if (empty($registration[$field])) {
            respond(['error' => "Missing required field: $field"], 400);
        }
    }

    // Check for duplicate
    $stmt = $db->prepare(
        'SELECT id FROM hackathon_registrations WHERE hackathon_id = ? AND user_email = ?'
    );
    $stmt->execute([$registration['hackathonId'], $registration['userEmail']]);
    if ($stmt->fetch()) {
        respond(['error' => 'User already registered for this hackathon'], 409);
    }

    // Build the registration ID
    $regId = $registration['id'] ?? ('reg_' . time());

    $stmt = $db->prepare(
        'INSERT INTO hackathon_registrations
            (id, hackathon_id, user_email, status, team_name, team_members, registration_date)
         VALUES (?, ?, ?, ?, ?, ?, NOW())'
    );
    $stmt->execute([
        $regId,
        $registration['hackathonId'],
        $registration['userEmail'],
        $registration['status']      ?? 'registered',
        $registration['teamName']    ?? null,
        isset($registration['teamMembers']) ? json_encode($registration['teamMembers']) : null
    ]);

    // Update participant count on the hackathon
    $db->prepare(
        'UPDATE hackathons SET current_participants = current_participants + 1 WHERE id = ?'
    )->execute([$registration['hackathonId']]);

    respond(['ok' => true, 'message' => 'Registration successful', 'registration' => $registration]);
}

function handleUpdate($input, $db) {
    $hackathonId = $input['hackathonId'] ?? '';
    $userEmail   = $input['userEmail']   ?? '';
    $status      = $input['status']      ?? '';

    if (!$hackathonId || !$userEmail) {
        respond(['error' => 'Hackathon ID and user email required'], 400);
    }

    $stmt = $db->prepare(
        'UPDATE hackathon_registrations SET status = ?, updated_at = NOW()
         WHERE hackathon_id = ? AND user_email = ?'
    );
    $stmt->execute([$status, $hackathonId, $userEmail]);

    if ($stmt->rowCount() === 0) {
        respond(['error' => 'Registration not found'], 404);
    }

    // If unregistered, decrement participant count
    if ($status === 'unregistered') {
        $db->prepare(
            'UPDATE hackathons SET current_participants = GREATEST(current_participants - 1, 0) WHERE id = ?'
        )->execute([$hackathonId]);
    }

    respond(['ok' => true, 'message' => 'Registration updated successfully']);
}

function handleGetUserRegistrations($input, $db) {
    $userEmail = $input['user_email'] ?? $input['userEmail'] ?? '';

    if (!$userEmail) {
        respond(['error' => 'User email required'], 400);
    }

    $stmt = $db->prepare(
        'SELECT r.*, h.title AS hackathon_title, h.event_date, h.status AS hackathon_status
         FROM hackathon_registrations r
         LEFT JOIN hackathons h ON h.id = r.hackathon_id
         WHERE r.user_email = ?
         ORDER BY r.registration_date DESC'
    );
    $stmt->execute([$userEmail]);
    $rows = $stmt->fetchAll();

    // Map column names to match the frontend expectations
    $registrations = [];
    foreach ($rows as $row) {
        $registrations[] = [
            'id'               => $row['id'],
            'hackathonId'      => $row['hackathon_id'],
            'userEmail'        => $row['user_email'],
            'status'           => $row['status'],
            'registrationDate' => $row['registration_date'],
            'hackathonTitle'   => $row['hackathon_title'],
            'eventDate'        => $row['event_date'],
            'hackathonStatus'  => $row['hackathon_status']
        ];
    }

    respond(['ok' => true, 'registrations' => $registrations]);
}

function handleGetHackathonRegistrations($input, $db) {
    $hackathonId = $input['hackathon_id'] ?? $input['hackathonId'] ?? '';

    if (!$hackathonId) {
        respond(['error' => 'Hackathon ID required'], 400);
    }

    $stmt = $db->prepare(
        'SELECT r.*, u.first_name, u.last_name, u.student_id
         FROM hackathon_registrations r
         LEFT JOIN users u ON u.email = r.user_email
         WHERE r.hackathon_id = ?
         ORDER BY r.registration_date ASC'
    );
    $stmt->execute([$hackathonId]);
    $rows = $stmt->fetchAll();

    $registrations = [];
    foreach ($rows as $row) {
        $registrations[] = [
            'id'               => $row['id'],
            'hackathonId'      => $row['hackathon_id'],
            'userEmail'        => $row['user_email'],
            'status'           => $row['status'],
            'registrationDate' => $row['registration_date'],
            'firstName'        => $row['first_name'],
            'lastName'         => $row['last_name'],
            'studentId'        => $row['student_id']
        ];
    }

    respond(['ok' => true, 'registrations' => $registrations]);
}

// Default response for unsupported methods
respond(['error' => 'Method not allowed'], 405);
?>
