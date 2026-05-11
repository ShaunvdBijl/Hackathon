<?php
// ============================================================
//  admin.php — Admin dashboard API
//  Now reads from MySQL tables via db.php
// ============================================================
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Simple admin authentication (enhance later with DB-stored admin accounts)
$admin_password = "admin123"; // Change this to a secure password

// Check if admin is authenticated (bypass for GET requests to allow data loading)
if (!isset($_SESSION['admin_authenticated']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['admin_authenticated'] = true;
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Admin authentication required']);
        exit;
    }
}

$action = $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {
    case 'login':
        if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
            $_SESSION['admin_authenticated'] = true;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid password']);
        }
        break;

    case 'logout':
        unset($_SESSION['admin_authenticated']);
        echo json_encode(['success' => true]);
        break;

    case 'submissions':
        $stmt = $db->query(
            'SELECT s.id, s.title, s.description AS `desc`, s.project_link AS link,
                    s.category, s.user_email, s.status, s.score, s.judge_notes,
                    s.ip_address, s.created_at AS createdAt,
                    u.first_name, u.last_name, u.student_id
             FROM submissions s
             LEFT JOIN users u ON u.email = s.user_email
             ORDER BY s.created_at DESC'
        );
        $submissions = $stmt->fetchAll();

        echo json_encode([
            'success'     => true,
            'submissions' => $submissions,
            'count'       => count($submissions)
        ]);
        break;

    case 'users':
        $stmt = $db->query(
            'SELECT id, first_name AS firstName, last_name AS lastName,
                    email, student_id AS studentId, role, created_at AS createdAt
             FROM users
             ORDER BY created_at DESC'
        );
        $users = $stmt->fetchAll();

        echo json_encode([
            'success' => true,
            'users'   => $users,
            'count'   => count($users)
        ]);
        break;

    case 'stats':
        $subCount  = $db->query('SELECT COUNT(*) FROM submissions')->fetchColumn();
        $userCount = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();

        echo json_encode([
            'success' => true,
            'stats'   => [
                'total_submissions' => (int)$subCount,
                'total_users'       => (int)$userCount,
                'submission_rate'   => $userCount > 0
                    ? round(($subCount / $userCount) * 100, 1)
                    : 0
            ]
        ]);
        break;

    case 'hackathons':
        $stmt = $db->query(
            'SELECT h.*,
                    (SELECT COUNT(*) FROM hackathon_registrations r
                     WHERE r.hackathon_id = h.id AND r.status = "registered") AS active_registrations,
                    (SELECT COUNT(*) FROM submissions s
                     WHERE s.hackathon_id = h.id) AS total_submissions
             FROM hackathons h
             ORDER BY h.created_at DESC'
        );
        $hackathons = $stmt->fetchAll();

        echo json_encode([
            'success'    => true,
            'hackathons' => $hackathons,
            'count'      => count($hackathons)
        ]);
        break;

    case 'registrations':
        $hackathonId = $_GET['hackathon_id'] ?? '';
        if ($hackathonId) {
            $stmt = $db->prepare(
                'SELECT r.*, u.first_name, u.last_name, u.student_id
                 FROM hackathon_registrations r
                 LEFT JOIN users u ON u.email = r.user_email
                 WHERE r.hackathon_id = ?
                 ORDER BY r.registration_date ASC'
            );
            $stmt->execute([$hackathonId]);
        } else {
            $stmt = $db->query(
                'SELECT r.*, u.first_name, u.last_name, u.student_id,
                        h.title AS hackathon_title
                 FROM hackathon_registrations r
                 LEFT JOIN users u ON u.email = r.user_email
                 LEFT JOIN hackathons h ON h.id = r.hackathon_id
                 ORDER BY r.registration_date DESC'
            );
        }
        $registrations = $stmt->fetchAll();

        echo json_encode([
            'success'       => true,
            'registrations' => $registrations,
            'count'         => count($registrations)
        ]);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
