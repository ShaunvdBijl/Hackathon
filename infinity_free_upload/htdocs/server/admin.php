<?php
session_start();
header('Content-Type: application/json');

// Simple admin authentication (you can enhance this later)
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
        // Get all submissions from the central file
        $submissions_file = __DIR__ . '/../data_store/all_submissions.json';
        if (file_exists($submissions_file)) {
            $submissions = json_decode(file_get_contents($submissions_file), true);
            $submissions = is_array($submissions) ? $submissions : [];
            echo json_encode([
                'success' => true,
                'submissions' => $submissions,
                'count' => count($submissions)
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'submissions' => [],
                'count' => 0
            ]);
        }
        break;
        
    case 'users':
        // Get all registered users
        $users_file = __DIR__ . '/../data_store/users.json';
        if (file_exists($users_file)) {
            $users = json_decode(file_get_contents($users_file), true);
            echo json_encode([
                'success' => true,
                'users' => $users,
                'count' => count($users)
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'users' => [],
                'count' => 0
            ]);
        }
        break;
        
    case 'stats':
        // Get overall statistics
        $submissions_file = __DIR__ . '/../data_store/all_submissions.json';
        $users_file = __DIR__ . '/../data_store/users.json';
        
        $submission_count = 0;
        $user_count = 0;
        
        if (file_exists($submissions_file)) {
            $submissions = json_decode(file_get_contents($submissions_file), true);
            $submissions = is_array($submissions) ? $submissions : [];
            $submission_count = count($submissions);
        }
        
        if (file_exists($users_file)) {
            $users = json_decode(file_get_contents($users_file), true);
            $user_count = count($users);
        }
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'total_submissions' => $submission_count,
                'total_users' => $user_count,
                'submission_rate' => $user_count > 0 ? round(($submission_count / $user_count) * 100, 1) : 0
            ]
        ]);
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
