<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Get user ID
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    header('Location: users.php');
    exit();
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header('Location: users.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View ID Card - Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
            }
            .id-card {
                page-break-inside: avoid;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark no-print">
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="messages.php">Messages</a>
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
        <div class="row no-print mb-4">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-id-card me-2"></i>ID Card Preview</h2>
                        <p class="text-muted mb-0">Viewing ID card for <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    </div>
                    <div>
                        <a href="users.php" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Users
                        </a>
                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-primary me-2">
                            <i class="fas fa-edit me-2"></i>Edit User
                        </a>
                        <button onclick="printIdCard()" class="btn btn-primary">
                            <i class="fas fa-print me-2"></i>Print ID Card
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ID Card Display -->
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="id-card">
                    <!-- Security Pattern Background -->
                    <div class="id-card-security-pattern"></div>
                    
                    <!-- ID Card Content -->
                    <div class="id-card-content">
                        <!-- Header -->
                        <div class="id-card-header">
                            <h1 class="id-card-title">Digital Identity Card</h1>
                            <p class="id-card-subtitle">Official Government Document</p>
                        </div>
                        
                        <!-- Body -->
                        <div class="id-card-body">
                            <!-- Photo Section -->
                            <div class="id-card-photo-section">
                                <?php if ($user['photo']): ?>
                                    <img src="../uploads/<?php echo htmlspecialchars($user['photo']); ?>"
                                         alt="Profile Photo" class="id-card-photo">
                                <?php else: ?>
                                    <div class="id-card-photo-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Information Section -->
                            <div class="id-card-info">
                                <div class="id-card-field">
                                    <span class="id-card-label">ID Number:</span>
                                    <span class="id-card-value">
                                        <span class="id-card-id-number"><?php echo htmlspecialchars($user['id_number']); ?></span>
                                    </span>
                                </div>
                                
                                <div class="id-card-field">
                                    <span class="id-card-label">Full Name:</span>
                                    <span class="id-card-value"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                </div>
                                
                                <div class="id-card-field">
                                    <span class="id-card-label">Date of Birth:</span>
                                    <span class="id-card-value"><?php echo date('F j, Y', strtotime($user['date_of_birth'])); ?></span>
                                </div>
                                
                                <div class="id-card-field">
                                    <span class="id-card-label">Position:</span>
                                    <span class="id-card-value"><?php echo htmlspecialchars($user['job_type']); ?></span>
                                </div>
                                
                                <div class="id-card-field">
                                    <span class="id-card-label">Issue Date:</span>
                                    <span class="id-card-value"><?php echo date('F j, Y', strtotime($user['created_at'])); ?></span>
                                </div>
                                
                                <div class="id-card-field">
                                    <span class="id-card-label">Email:</span>
                                    <span class="id-card-value"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="id-card-footer">
                            <p>This is an official digital identity card issued by the ID Card System</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="row mt-4 no-print">
            <div class="col">
                <div class="card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-user-circle me-2"></i>User Details</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm user-details-table mb-0">
                                <tbody>
                                    <tr>
                                        <td class="detail-label">User ID</td>
                                        <td class="detail-value">#<?php echo $user['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">First Name</td>
                                        <td class="detail-value"><?php echo htmlspecialchars($user['first_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">Last Name</td>
                                        <td class="detail-value"><?php echo htmlspecialchars($user['last_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">Email</td>
                                        <td class="detail-value"><?php echo htmlspecialchars($user['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">Date of Birth</td>
                                        <td class="detail-value"><?php echo date('F j, Y', strtotime($user['date_of_birth'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">Job Type</td>
                                        <td class="detail-value"><?php echo htmlspecialchars($user['job_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">ID Number</td>
                                        <td class="detail-value">
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($user['id_number']); ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">Account Created</td>
                                        <td class="detail-value"><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">Last Updated</td>
                                        <td class="detail-value"><?php echo date('M j, Y g:i A', strtotime($user['updated_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="detail-label">Photo Status</td>
                                        <td class="detail-value">
                                            <?php if ($user['photo']): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Available
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>No Photo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printIdCard() {
            window.print();
        }
    </script>
</body>
</html>
