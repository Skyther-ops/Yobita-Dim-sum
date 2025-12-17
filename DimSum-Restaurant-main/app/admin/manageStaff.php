<?php
session_start();
require_once '../connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pageTitle = "Manage waiter";
$basePath = "../";
include '../_header.php';

$message = "";
$message_type = "";

// --- HANDLE DELETE STAFF ---
if (isset($_POST['delete_staff'])) {
    $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
    
    if ($staff_id) {
        if ($staff_id == $_SESSION['user_id']) {
            $message = "You cannot delete your own account.";
            $message_type = "error";
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM staffs WHERE id = ?");
                $stmt->execute([$staff_id]);
                
                if ($stmt->rowCount() > 0) {
                    $message = "Staff member deleted successfully.";
                    $message_type = "success";
                } else {
                    $message = "Staff member not found.";
                    $message_type = "error";
                }
            } catch (PDOException $e) {
                $message = "Error deleting staff: " . $e->getMessage();
                $message_type = "error";
            }
        }
    }
}

// --- FETCH staffs & ORDER COUNTS ---
try {
    // We select staffs and count their orders by joining the orders table
    $sql = "SELECT 
                u.id, 
                u.username, 
                u.email, 
                u.role, 
                u.profile_picture, 
                u.created_at,
                COUNT(o.id) as orders_handled
            FROM staffs u
            LEFT JOIN orders o ON u.id = o.user_id
            GROUP BY u.id
            ORDER BY u.role ASC, u.username ASC";
            
    $stmt = $pdo->query($sql);
    $waiter_members = $stmt->fetchAll();
} catch (PDOException $e) {
    $waiter_members = [];
    $message = "Error fetching waiter: " . $e->getMessage();
    $message_type = "error";
}
?>

<link rel="stylesheet" href="<?php echo $basePath; ?>css/viewOrders.css">
<link rel="stylesheet" href="<?php echo $basePath; ?>css/manageStaff.css">
<style>
    /* Enhanced Table & UI Styles */
    .admin-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
    
    .page-header {
        background: white;
        padding: 20px 30px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .page-header h1 { margin: 0; font-size: 1.5rem; color: #2d3748; }
    
    .add-btn {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        color: white;
        padding: 10px 20px;
        border-radius: 30px;
        text-decoration: none;
        font-weight: 600;
        box-shadow: 0 4px 10px rgba(28, 200, 138, 0.3);
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .add-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(28, 200, 138, 0.4); color: white; }

    .orders-list-section {
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        padding: 25px;
        overflow: hidden;
    }
    
    table { border-collapse: separate; border-spacing: 0 10px; }
    thead th { border-bottom: 2px solid #edf2f7; color: #718096; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; padding-bottom: 15px; }
    tbody tr { transition: transform 0.2s; background: white; }
    tbody tr:hover { transform: scale(1.01); box-shadow: 0 5px 15px rgba(0,0,0,0.05); z-index: 10; position: relative; }
    tbody td { border-bottom: 1px solid #edf2f7; padding: 15px; vertical-align: middle; }
    
    .delete-icon-btn {
        background: #fee2e2;
        color: #e53e3e;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .delete-icon-btn:hover { background: #e53e3e; color: white; }
</style>

<main class="main-wrapper">
    <div class="admin-container">
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
        
        <div class="page-header">
            <h1>Staff Directory</h1>
            <a href="addStaff.php" class="add-btn">
                <ion-icon name="person-add-outline"></ion-icon> Add New Staff
            </a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message" style="padding: 15px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-weight: 500; <?php echo $message_type === 'success' ? 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;' : 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="orders-list-section">
            <h2 class="section-title">All staffs (<?php echo count($waiter_members); ?>)</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Orders Handled</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($waiter_members as $waiter): ?>
                            <tr>
                                <td data-label="Profile">
                                    <img src="<?php echo $waiter['profile_picture'] ? $basePath . $waiter['profile_picture'] : $basePath.'uploads/Default_pfp.png'; ?>" 
                                         alt="pfp" class="table-pfp">
                                </td>
                                <td data-label="Name"><strong><?php echo htmlspecialchars($waiter['username']); ?></strong></td>
                                <td data-label="Role">
                                    <span class="role-badge role-<?php echo $waiter['role']; ?>">
                                        <?php echo ucfirst($waiter['role']); ?>
                                    </span>
                                </td>
                                <td data-label="Email"><?php echo htmlspecialchars($waiter['email']); ?></td>
                                <td data-label="Orders Handled"><?php echo $waiter['orders_handled']; ?></td>
                                <td data-label="Joined"><?php echo date("d M Y", strtotime($waiter['created_at'])); ?></td>
                                <td class="actions-cell" data-label="Actions">
                                    <a href="staffDetail.php?id=<?php echo $waiter['id']; ?>" class="action-btn view-btn">
                                        View Details
                                    </a>
                                    <form method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                        <input type="hidden" name="staff_id" value="<?php echo $waiter['id']; ?>">
                                        <button type="submit" name="delete_staff" class="delete-icon-btn" title="Delete Staff">
                                            <ion-icon name="trash-outline"></ion-icon>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>