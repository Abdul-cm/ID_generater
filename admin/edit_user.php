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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Security token validation failed. Please try again.';
    } else {
        $first_name = sanitize_input($_POST['first_name']);
        $last_name = sanitize_input($_POST['last_name']);
        $email = sanitize_input($_POST['email']);
        $date_of_birth = sanitize_input($_POST['date_of_birth']);
        $job_type = sanitize_input($_POST['job_type']);

        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($date_of_birth) || empty($job_type)) {
            $error = 'All fields are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } else {
            // Check if email already exists for other users
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = 'Email already exists for another user.';
            } else {
                $photo_name = $user['photo']; // Keep existing photo by default
                
                // Handle photo upload if new photo is provided
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/';
                    
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $allowed_types = ALLOWED_IMAGE_TYPES;
                    $max_size = MAX_FILE_SIZE;

                    if (in_array($_FILES['photo']['type'], $allowed_types) && $_FILES['photo']['size'] <= $max_size) {
                        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                        $new_photo_name = uniqid() . '.' . $file_extension;
                        $upload_path = $upload_dir . $new_photo_name;
                        
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                            // Delete old photo if it exists
                            if ($photo_name && file_exists($upload_dir . $photo_name)) {
                                unlink($upload_dir . $photo_name);
                            }
                            $photo_name = $new_photo_name;
                        } else {
                            $error = 'Failed to upload photo. Please try again.';
                        }
                    } else {
                        $error = 'Invalid photo format or size. Please upload a JPG, JPEG, or PNG file under ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB.';
                    }
                }
                
                if (empty($error)) {
                    // Update user in database
                    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, date_of_birth = ?, job_type = ?, photo = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $date_of_birth, $job_type, $photo_name, $user_id);
                    
                    if ($stmt->execute()) {
                        $success = 'User updated successfully!';
                        
                        // Refresh user data
                        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $user = $stmt->get_result()->fetch_assoc();
                    } else {
                        $error = 'Failed to update user. Please try again.';
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin Panel</title>
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
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-shield-alt me-2"></i>Admin Panel</h2>
                        <p class="text-muted mb-0">Update user information and ID card details</p>
                    </div>
                    <div>
                        <a href="users.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Users
                        </a>
                        <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-info">
                            <i class="fas fa-id-card me-2"></i>View ID Card
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2"></i>
                            Editing: <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                           <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth *</label>
                                    <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                           value="<?php echo $user['date_of_birth']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="job_type" class="form-label">Job Type *</label>
                                    <select class="form-control" id="job_type" name="job_type" required>
                                        <option value="">Select Job Type</option>
                                        <option value="Student" <?php echo ($user['job_type'] === 'Student') ? 'selected' : ''; ?>>Student</option>
                                        <option value="Employee" <?php echo ($user['job_type'] === 'Employee') ? 'selected' : ''; ?>>Employee</option>
                                        <option value="Teacher" <?php echo ($user['job_type'] === 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
                                        <option value="Manager" <?php echo ($user['job_type'] === 'Manager') ? 'selected' : ''; ?>>Manager</option>
                                        <option value="Director" <?php echo ($user['job_type'] === 'Director') ? 'selected' : ''; ?>>Director</option>
                                        <option value="Other" <?php echo ($user['job_type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Current Photo</label>
                                <div class="mb-2">
                                    <?php if ($user['photo']): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                                             alt="Current Photo" class="photo-preview">
                                    <?php else: ?>
                                        <div class="photo-preview d-flex align-items-center justify-content-center bg-secondary text-white">
                                            <i class="fas fa-user fa-3x"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="photo" class="form-label">Update Photo (Optional)</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                <small class="form-text text-muted">Upload JPG, JPEG, or PNG file (Max 2MB). Leave empty to keep current photo.</small>
                                <div class="mt-2">
                                    <img id="photoPreview" class="photo-preview" style="display: none;" alt="Photo Preview">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Account Information</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>ID Number:</strong> 
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($user['id_number']); ?></span>
                                        </p>
                                        <p><strong>Account Created:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Last Updated:</strong> <?php echo date('F j, Y g:i A', strtotime($user['updated_at'])); ?></p>
                                        <p><strong>User ID:</strong> #<?php echo $user['id']; ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="users.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                                </a>
                                <div>
                                    <a href="view_user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline-info me-2">
                                        <i class="fas fa-id-card me-2"></i>View ID Card
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update User
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
