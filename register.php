<?php
require_once 'includes/config.php';

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Security token validation failed. Please try again.';
    } else {
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $first_name = sanitize_input($_POST['first_name'] ?? '');
        $last_name = sanitize_input($_POST['last_name'] ?? '');
        $date_of_birth = sanitize_input($_POST['date_of_birth'] ?? '');
        $job_type = sanitize_input($_POST['job_type'] ?? '');

        // Validate date of birth - not in future and at least 18 years old
        $dob_timestamp = strtotime($date_of_birth);
        $current_timestamp = time();
        $min_age_timestamp = strtotime('-18 years', $current_timestamp);

        // Enhanced validation
        if (empty($email) || empty($password) || empty($first_name) || empty($last_name) ||
            empty($date_of_birth) || empty($job_type)) {
            $error = 'All fields are required.';
        } elseif ($dob_timestamp === false) {
            $error = 'Invalid date of birth.';
        } elseif ($dob_timestamp > $current_timestamp) {
            $error = 'Date of birth cannot be in the future.';
        } elseif ($dob_timestamp > $min_age_timestamp) {
            $error = 'You must be at least 18 years old to register.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
            $error = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long.';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)/', $password)) {
            $error = 'Password must contain at least one lowercase letter, one uppercase letter, and one number.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please upload a photo.';
        } else {
            // Validate image upload
            $upload_errors = validate_image_upload($_FILES['photo']);
            if (!empty($upload_errors)) {
                $error = implode('<br>', $upload_errors);
            } else {
                // Check if email already exists
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $error = 'Email already exists. Please use a different email.';
                } else {
                    // Handle secure photo upload
                    $upload_dir = 'uploads/';

                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }

                    // Generate secure filename
                    $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $photo_name = 'profile_' . bin2hex(random_bytes(16)) . '.' . $file_extension;
                    $photo_path = $upload_dir . $photo_name;

                    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path)) {
                        $error = 'Failed to upload photo. Please try again.';
                    } else {
                        // Generate unique ID number
                        do {
                            $id_number = 'ID' . date('Y') . sprintf('%06d', random_int(100000, 999999));
                            $check_stmt = $conn->prepare("SELECT id FROM users WHERE id_number = ?");
                            $check_stmt->bind_param("s", $id_number);
                            $check_stmt->execute();
                            $check_result = $check_stmt->get_result();
                        } while ($check_result && $check_result->num_rows > 0);

                        // Hash password securely
                        $hashed_password = secure_hash_password($password);

                        // Insert user into database
                        $insert_stmt = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, date_of_birth, job_type, photo, id_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $insert_stmt->bind_param("ssssssss", $email, $hashed_password, $first_name, $last_name, $date_of_birth, $job_type, $photo_name, $id_number);

                        if ($insert_stmt->execute()) {
                            $success = 'Registration successful! Your ID Number is: ' . $id_number . '. Please save this number. You can now login with your email and password.';
                        } else {
                            $error = 'Registration failed. Please try again.';
                            // Delete uploaded photo if database insertion failed
                            if (file_exists($photo_path)) {
                                unlink($photo_path);
                            }
                        }
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
    <title>User Registration - ID Card System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-id-card me-2"></i>ID Card System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="register.php">Register</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Registration</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <div class="mt-3">
                                    <a href="login.php" class="btn btn-primary">Login Now</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <form id="registrationForm" method="POST" action="" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="firstName" class="form-label">First Name *</label>
                                        <input type="text" class="form-control" id="firstName" name="first_name" 
                                               value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="lastName" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control" id="lastName" name="last_name" 
                                               value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password *</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <small class="form-text text-muted">Minimum 8 characters with uppercase, lowercase, and number</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="confirmPassword" class="form-label">Confirm Password *</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="dateOfBirth" class="form-label">Date of Birth *</label>
                                        <input type="date" class="form-control" id="dateOfBirth" name="date_of_birth" 
                                               value="<?php echo isset($_POST['date_of_birth']) ? $_POST['date_of_birth'] : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="jobType" class="form-label">Job Type *</label>
                                        <select class="form-control" id="jobType" name="job_type" required>
                                            <option value="">Select Job Type</option>
                                            <option value="Student" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Student') ? 'selected' : ''; ?>>Student</option>
                                            <option value="Employee" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Employee') ? 'selected' : ''; ?>>Employee</option>
                                            <option value="Teacher" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Teacher') ? 'selected' : ''; ?>>Teacher</option>
                                            <option value="Manager" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Manager') ? 'selected' : ''; ?>>Manager</option>
                                            <option value="Director" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Director') ? 'selected' : ''; ?>>Director</option>
                                            <option value="Other" <?php echo (isset($_POST['job_type']) && $_POST['job_type'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="photo" class="form-label">Photo *</label>
                                    <input type="file" class="form-control" id="photo" name="photo" accept="image/*" required>
                                    <small class="form-text text-muted">Upload JPG, JPEG, or PNG file (Max 2MB)</small>
                                    <div class="mt-2">
                                        <img id="photoPreview" class="photo-preview" style="display: none;" alt="Photo Preview">
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-user-plus me-2"></i>Create Account
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <p>Already have an account? <a href="login.php">Login here</a></p>
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
