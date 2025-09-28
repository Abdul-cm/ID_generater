<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Handle user deletion (with CSRF check)
if (isset($_POST['delete_user']) && isset($_POST['user_id'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Security token validation failed. Please try again.';
    } else {
        $user_id = (int)$_POST['user_id'];

        // Get user photo to delete
        $stmt = $conn->prepare("SELECT photo FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Delete user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                // Delete photo file
                if (!empty($user['photo']) && file_exists('../uploads/' . $user['photo'])) {
                    @unlink('../uploads/' . $user['photo']);
                }
                $success = 'User deleted successfully!';
            } else {
                $error = 'Failed to delete user.';
            }
        } else {
            $error = 'User not found.';
        }
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$search_sql = '';
$search_params = [];

if ($search) {
    $search_sql = "WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR job_type LIKE ? OR id_number LIKE ?";
    $search_term = "%$search%";
    $search_params = [$search_term, $search_term, $search_term, $search_term, $search_term];
}

// Count total users
$count_sql = "SELECT COUNT(*) as total FROM users $search_sql";
if ($search_params) {
    $stmt = $conn->prepare($count_sql);
    $stmt->bind_param("sssss", ...$search_params);
    $stmt->execute();
    $total_users = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_users = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_users / $per_page);

// Get users
$users_sql = "SELECT * FROM users $search_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
if ($search_params) {
    $stmt = $conn->prepare($users_sql);
    $all_params = array_merge($search_params, [$per_page, $offset]);
    $param_types = str_repeat('s', count($search_params)) . 'ii';
    $stmt->bind_param($param_types, ...$all_params);
} else {
    $stmt = $conn->prepare($users_sql);
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Panel</title>
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
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-shield-alt me-2"></i>Admin Panel</h2>
                <p class="text-muted">View, edit, and manage all registered users and their ID cards.</p>
            </div>
        </div>

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

        <!-- Search and Stats -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form method="GET" action="" class="d-flex">
                    <input type="text" class="form-control me-2" name="search" placeholder="Search users..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if ($search): ?>
                        <a href="users.php" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-0 text-muted">
                    Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_users); ?> 
                    of <?php echo $total_users; ?> users
                </p>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>ID Number</th>
                                <th>Job Type</th>
                                <th>Date of Birth</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users->num_rows > 0): ?>
                                <?php while ($user = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php if ($user['photo']): ?>
                                                <img src="../uploads/<?php echo htmlspecialchars($user['photo']); ?>" 
                                                     alt="Photo" class="rounded-circle" width="40" height="40" style="object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($user['id_number']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['job_type']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['date_of_birth'])); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-primary" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="view_user.php?id=<?php echo $user['id']; ?>" 
                                                   class="btn btn-outline-info" title="View ID Card">
                                                    <i class="fas fa-id-card"></i>
                                                </a>
                                                <button type="button" class="btn btn-outline-danger" 
                                                        onclick="confirmDelete(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>')" 
                                                        title="Delete User">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                        <p class="text-muted">No users found.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete the user <strong id="userName"></strong>? 
                    This action cannot be undone and will also delete their ID card and photo.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="user_id" id="deleteUserId">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <button type="submit" name="delete_user" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete User
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(userId, userName) {
            document.getElementById('deleteUserId').value = userId;
            document.getElementById('userName').textContent = userName;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
