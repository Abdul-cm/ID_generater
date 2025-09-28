<?php
require_once 'includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit();
}

// Check for message parameter
$message = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'edit_restricted') {
        $message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-lock me-2"></i>
            <strong>Access Restricted:</strong> For security and data integrity purposes, only administrators can edit profile information.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - ID Card System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
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
                <i class="fas fa-id-card me-2"></i>ID Card System
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($user['first_name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <?php echo $message; ?>
        
        <!-- Welcome Section -->
        <div class="row">
            <div class="col">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h4><i class="fas fa-user me-2"></i>User Dashboard</h4>
                        <p class="mb-3 text-muted">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>! Your registration is complete. Here are your details:</p>
                        
                        <!-- ID Number Display -->
                        <div class="alert alert-success">
                            <h3 class="mb-0">
                                <i class="fas fa-id-card me-2"></i>Your ID Number: 
                                <span class="id-number"><?php echo htmlspecialchars($user['id_number']); ?></span>
                            </h3>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Important:</strong> Please save your ID number. The admin will create and manage your ID card.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ID Card Display -->
        <div class="row justify-content-center mb-5">
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
                                    <img src="uploads/<?php echo htmlspecialchars($user['photo']); ?>"
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

        <!-- Profile Information -->
        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Your Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Personal Information -->
                            <div class="col-md-6">
                                <div class="profile-info-section">
                                    <h6 class="text-primary mb-3"><i class="fas fa-address-card me-2"></i>Personal Details</h6>
                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user text-muted me-3" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Full Name</small>
                                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-envelope text-muted me-3" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Email Address</small>
                                                <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-birthday-cake text-muted me-3" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Date of Birth</small>
                                                <strong><?php echo date('F j, Y', strtotime($user['date_of_birth'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Information -->
                            <div class="col-md-6">
                                <div class="profile-info-section">
                                    <h6 class="text-success mb-3"><i class="fas fa-briefcase me-2"></i>Account Details</h6>
                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-muted me-3" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Job Type</small>
                                                <span class="badge bg-info"><?php echo htmlspecialchars($user['job_type']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-id-badge text-muted me-3" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">ID Number</small>
                                                <span class="badge bg-primary fs-6 px-3 py-2"><?php echo htmlspecialchars($user['id_number']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-plus text-muted me-3" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Account Created</small>
                                                <strong><?php echo date('F j, Y', strtotime($user['created_at'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-item mb-0">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-clock text-muted me-3" style="width: 20px;"></i>
                                            <div>
                                                <small class="text-muted d-block">Last Updated</small>
                                                <strong><?php echo date('F j, Y', strtotime($user['updated_at'])); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
