<?php
session_start();
require_once '../connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = "";
$message_type = "";

if (isset($_POST['add_staff'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (!empty($username) && !empty($email) && !empty($password) && !empty($role)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM staffs WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $message = "Email already exists!";
                $message_type = "error";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $default_pfp = 'uploads/Default_pfp.png';
                
                $sql = "INSERT INTO staffs (username, email, password, role, profile_picture) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$username, $email, $hashed_password, $role, $default_pfp])) {
                    $message = "New " . htmlspecialchars(ucfirst($role)) . " added successfully!";
                    $message_type = "success";
                } else {
                    $message = "Failed to add staff member.";
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

$pageTitle = "Add Staff";
$basePath = "../";
include '../_header.php';
?>

<link rel="stylesheet" href="<?php echo $basePath; ?>css/addMenu.css">
<style>
    /* Modern UI Overrides */
    .main-wrapper {
        background-color: #f8f9fc;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px 20px;
    }
    .admin-container {
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
    }
    .form-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        animation: slideUp 0.5s ease-out;
    }
    .page-header h1 {
        text-align: center;
        color: #2d3748;
        font-size: 1.8rem;
        margin-bottom: 10px;
    }
    .page-header p {
        text-align: center;
        color: #718096;
        margin-bottom: 30px;
    }
    .form-group {
        margin-bottom: 20px;
        position: relative;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #4a5568;
        font-size: 0.9rem;
    }
    .form-group input, .form-group select {
        width: 100%;
        padding: 12px 15px 12px 40px; /* Space for icon */
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s;
        background-color: #fcfcfc;
    }
    .form-group input:focus, .form-group select:focus {
        border-color: #4e73df;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        outline: none;
    }
    .input-icon {
        position: absolute;
        left: 15px;
        top: 42px;
        color: #a0aec0;
        font-size: 1.2rem;
    }
    .submit-btn {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-top: 10px;
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
    }
    .back-link {
        display: inline-block;
        margin-bottom: 20px;
        color: #718096;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }
    .back-link:hover { color: #4e73df; }
    
    @keyframes slideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<main class="main-wrapper">
    <div class="admin-container">
        <div class="form-card">
            <a href="manageStaff.php" class="back-link">← Back to Staff List</a>
            
            <div class="page-header">
                <h1>Register New Staff</h1>
                <p>Create a new account for a waiter or chef</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message <?php echo $message_type; ?>" style="margin-bottom: 20px; padding: 12px; border-radius: 8px; text-align: center; font-size: 0.9rem; <?php echo $message_type === 'success' ? 'background:#d1fae5; color:#065f46;' : 'background:#fee2e2; color:#991b1b;'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Username</label>
                    <ion-icon name="person-outline" class="input-icon"></ion-icon>
                    <input type="text" name="username" required placeholder="e.g. John Doe">
                </div>
                
                <div class="form-group">
                    <label>Email Address</label>
                    <ion-icon name="mail-outline" class="input-icon"></ion-icon>
                    <input type="email" name="email" required placeholder="e.g. john@yobita.com">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <ion-icon name="lock-closed-outline" class="input-icon"></ion-icon>
                    <input type="password" name="password" required placeholder="••••••">
                </div>
                
                <div class="form-group">
                    <label>Role</label>
                    <ion-icon name="briefcase-outline" class="input-icon"></ion-icon>
                    <select name="role" required>
                        <option value="waiter">Waiter</option>
                        <option value="chef">Chef</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <button type="submit" name="add_staff" class="submit-btn">Register Staff</button>
            </form>
        </div>
    </div>
</main>