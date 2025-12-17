<?php
session_start();
require_once '../connection.php';

// --- ACCESS CONTROL ---
// Ensure user is logged in. 
// Check if the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$message = "";
$message_type = "";

// --- HANDLE FORM SUBMISSION ---
if (isset($_POST['add_staff'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($password) && !empty($role)) {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Username already exists!";
                $message_type = "error";
            } else {
                // Hash password and insert new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                if ($stmt->execute([$username, $hashed_password, $role])) {
                    $message = "New " . htmlspecialchars($role) . " added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to add staff.";
                    $message_type = "error";
                }
            }
        } catch (PDOException $e) {
            $message = "Database error: " . $e->getMessage();
            $message_type = "error";
        }
    } else {
        $message = "Please fill in all fields.";
        $message_type = "error";
    }
}

$pageTitle = "Manage Staff";
$basePath = "../";
include '../_header.php';
?>
<link rel="stylesheet" href="../css/menu.css">
<style>
        .admin-wrapper { max-width: 500px; margin: 100px auto; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 16px; }
        .btn-submit { width: 100%; padding: 12px; background-color: var(--primary, #ff6b6b); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
        .btn-submit:hover { opacity: 0.9; }
        .msg-success { background-color: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .msg-error { background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .back-link-container { text-align: center; margin-top: 20px; }
    </style>
    <div class="admin-wrapper">
        <h2 style="text-align:center; margin-bottom: 25px; color: #333;">Add New Staff</h2>
        
        <?php if ($message): ?>
            <div class="<?php echo $message_type === 'success' ? 'msg-success' : 'msg-error'; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group"><label>Username</label><input type="text" name="username" required placeholder="e.g., waiter_john"></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required placeholder="******"></div>
            <div class="form-group"><label>Role</label><select name="role"><option value="waiter">Waiter</option><option value="chef">Chef</option><option value="admin">Admin</option></select></div>
            <button type="submit" name="add_staff" class="btn-submit">Add Staff Member</button>
        </form>
        <div class="back-link-container"><a href="../menu/menu.php" style="color: #666; text-decoration: none;">&larr; Back to Menu</a></div>
    </div>