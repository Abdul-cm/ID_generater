<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get statistics
$stats = [];

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$stats['total_users'] = $stmt->get_result()->fetch_assoc()['total_users'];

// Total messages
$stmt = $conn->prepare("SELECT COUNT(*) as total_messages FROM messages");
$stmt->execute();
$stats['total_messages'] = $stmt->get_result()->fetch_assoc()['total_messages'];

// Unread messages
$stmt = $conn->prepare("SELECT COUNT(*) as unread_messages FROM messages WHERE status = 'unread'");
$stmt->execute();
$stats['unread_messages'] = $stmt->get_result()->fetch_assoc()['unread_messages'];

// Recent registrations (last 30 days)
$stmt = $conn->prepare("SELECT COUNT(*) as recent_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$stmt->execute();
$stats['recent_users'] = $stmt->get_result()->fetch_assoc()['recent_users'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ID Card System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-shield-alt me-2"></i>Admin Panel
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">Messages 
                            <?php if ($stats['unread_messages'] > 0): ?>
                                <span class="badge bg-danger"><?php echo $stats['unread_messages']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-shield me-1"></i><?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../index.php" target="_blank"><i class="fas fa-home me-2"></i>View Site</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-shield-alt me-2"></i>Admin Panel</h2>
                <p class="text-muted">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>! Here's what's happening with your ID Card System.</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stats-label">
                        <i class="fas fa-users me-2"></i>Total Users
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stats-label">
                        <i class="fas fa-id-card me-2"></i>ID Cards Generated
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-number text-warning"><?php echo $stats['unread_messages']; ?></div>
                    <div class="stats-label">
                        <i class="fas fa-envelope me-2"></i>Unread Messages
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stats-card">
                    <div class="stats-number text-success"><?php echo $stats['recent_users']; ?></div>
                    <div class="stats-label">
                        <i class="fas fa-user-plus me-2"></i>New Users (30 days)
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row mb-5">
            <div class="col">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <a href="users.php" class="btn btn-outline-primary btn-lg w-100">
                                    <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                    Manage Users
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="messages.php" class="btn btn-outline-success btn-lg w-100 position-relative">
                                    <i class="fas fa-envelope fa-2x mb-2 d-block"></i>
                                    View Messages
                                    <?php if ($stats['unread_messages'] > 0): ?>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            <?php echo $stats['unread_messages']; ?>
                                        </span>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="col-md-4 mb-3">
                                <a href="../index.php" target="_blank" class="btn btn-outline-info btn-lg w-100">
                                    <i class="fas fa-external-link-alt fa-2x mb-2 d-block"></i>
                                    View Website
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Recent Users</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $conn->query("SELECT first_name, last_name, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
                        if ($stmt->num_rows > 0):
                        ?>
                            <div class="list-group list-group-flush">
                                <?php while ($user = $stmt->fetch_assoc()): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo date('M j', strtotime($user['created_at'])); ?></small>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="users.php" class="btn btn-sm btn-outline-primary">View All Users</a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No users found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Recent Messages</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $conn->query("SELECT first_name, last_name, email, message, status, created_at FROM messages ORDER BY created_at DESC LIMIT 5");
                        if ($stmt->num_rows > 0):
                        ?>
                            <div class="list-group list-group-flush">
                                <?php while ($message = $stmt->fetch_assoc()): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-bold">
                                                <?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?>
                                                <?php if ($message['status'] === 'unread'): ?>
                                                    <span class="badge bg-warning">New</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($message['email']); ?></small>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($message['message'], 0, 50)) . '...'; ?></small>
                                        </div>
                                        <small class="text-muted"><?php echo date('M j', strtotime($message['created_at'])); ?></small>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="messages.php" class="btn btn-sm btn-outline-primary">View All Messages</a>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">No messages found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
