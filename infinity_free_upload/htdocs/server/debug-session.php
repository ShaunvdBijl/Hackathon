<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Debug Session Info</title>
    <style>
        body { font-family: monospace; padding: 2rem; background: #f5f5f5; }
        .debug-box { background: white; padding: 1rem; margin: 1rem 0; border-radius: 8px; border: 1px solid #ddd; }
        .success { color: #28a745; } .error { color: #dc3545; } .warning { color: #ffc107; }
        pre { background: #f8f9fa; padding: 1rem; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🐛 Debug Session & File System</h1>
    
    <div class="debug-box">
        <h3>📋 Session Information</h3>
        <pre><?php var_dump($_SESSION); ?></pre>
        
        <p><strong>Admin Authenticated:</strong> 
            <span class="<?php echo (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']) ? 'success' : 'error'; ?>">
                <?php echo (isset($_SESSION['admin_authenticated']) && $_SESSION['admin_authenticated']) ? 'YES' : 'NO'; ?>
            </span>
        </p>
        
        <p><strong>User Email:</strong> 
            <span class="<?php echo isset($_SESSION['user_email']) ? 'success' : 'error'; ?>">
                <?php echo $_SESSION['user_email'] ?? 'NOT SET'; ?>
            </span>
        </p>
    </div>
    
    <div class="debug-box">
        <h3>📁 File System Paths</h3>
        <?php
        $uploadDir = __DIR__ . '/../uploads';
        $realUploadDir = realpath($uploadDir);
        $testUserDir = $uploadDir . '/test_student_com';
        $realTestUserDir = realpath($testUserDir);
        ?>
        
        <p><strong>Upload Directory:</strong> <?php echo $uploadDir; ?></p>
        <p><strong>Real Upload Directory:</strong> 
            <span class="<?php echo $realUploadDir ? 'success' : 'error'; ?>">
                <?php echo $realUploadDir ?: 'NOT FOUND'; ?>
            </span>
        </p>
        
        <p><strong>Test User Directory:</strong> <?php echo $testUserDir; ?></p>
        <p><strong>Real Test User Directory:</strong> 
            <span class="<?php echo $realTestUserDir ? 'success' : 'error'; ?>">
                <?php echo $realTestUserDir ?: 'NOT FOUND'; ?>
            </span>
        </p>
        
        <p><strong>Directory Exists:</strong> 
            <span class="<?php echo is_dir($testUserDir) ? 'success' : 'error'; ?>">
                <?php echo is_dir($testUserDir) ? 'YES' : 'NO'; ?>
            </span>
        </p>
    </div>
    
    <div class="debug-box">
        <h3>📄 Test Files</h3>
        <?php
        $testFiles = [
            '2025-01-04_16-30-00_abc123_project-source.zip',
            '2025-01-04_16-30-05_def456_documentation.rar'
        ];
        
        foreach ($testFiles as $fileName) {
            $filePath = $testUserDir . '/' . $fileName;
            $realFilePath = realpath($filePath);
            $exists = file_exists($filePath);
            $size = $exists ? filesize($filePath) : 0;
            
            echo "<div style='margin: 0.5rem 0; padding: 0.5rem; background: " . ($exists ? '#d4edda' : '#f8d7da') . ";'>";
            echo "<strong>$fileName:</strong><br>";
            echo "Path: $filePath<br>";
            echo "Real Path: " . ($realFilePath ?: 'NOT FOUND') . "<br>";
            echo "Exists: " . ($exists ? 'YES' : 'NO') . "<br>";
            echo "Size: " . ($exists ? $size . ' bytes' : 'N/A') . "<br>";
            echo "</div>";
        }
        ?>
    </div>
    
    <div class="debug-box">
        <h3>🔗 Test Download Links</h3>
        <p>Click these links to test downloads:</p>
        
        <p><a href="download.php?file=2025-01-04_16-30-00_abc123_project-source.zip&user=test@student.com" target="_blank">
            📁 Download project-source.zip
        </a></p>
        
        <p><a href="download.php?file=2025-01-04_16-30-05_def456_documentation.rar&user=test@student.com" target="_blank">
            📁 Download documentation.rar
        </a></p>
    </div>
    
    <div class="debug-box">
        <h3>🔐 Admin Login Test</h3>
        <form method="post" action="admin.php?action=login">
            <input type="password" name="password" placeholder="Enter admin123" style="padding: 0.5rem; margin-right: 0.5rem;">
            <button type="submit" style="padding: 0.5rem 1rem;">Login as Admin</button>
        </form>
    </div>
    
    <div class="debug-box">
        <h3>🔄 Quick Actions</h3>
        <p><a href="debug-session.php">🔄 Refresh This Page</a></p>
        <p><a href="../admin.html">🏆 Go to Admin Panel</a></p>
        <p><a href="../test-admin-download.html">🧪 Go to Download Test</a></p>
    </div>
</body>
</html>
