<?php
require_once '../includes/config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

$success = '';
$error = '';

// Handle message actions (with CSRF check)
if (isset($_POST['action']) && isset($_POST['message_id'])) {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Security token validation failed. Please try again.';
    } else {
        $message_id = (int)$_POST['message_id'];
        $action = $_POST['action'];

        if ($action === 'mark_read') {
            $stmt = $conn->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
            $stmt->bind_param("i", $message_id);

            if ($stmt->execute()) {
                $success = 'Message marked as read!';
            } else {
                $error = 'Failed to update message status.';
            }
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->bind_param("i", $message_id);

            if ($stmt->execute()) {
                $success = 'Message deleted successfully!';
            } else {
                $error = 'Failed to delete message.';
            }
        }
    }
}

// Get messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

$where_conditions = [];
$params = [];

if ($filter === 'unread') {
    $where_conditions[] = "status = 'unread'";
} elseif ($filter === 'read') {
    $where_conditions[] = "status = 'read'";
}

if ($search) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
}

$where_sql = $where_conditions ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Count total messages
$count_sql = "SELECT COUNT(*) as total FROM messages $where_sql";
if ($params) {
    $stmt = $conn->prepare($count_sql);
    if ($search && $filter !== 'all') {
        $stmt->bind_param("ssss", ...$params);
    } elseif ($search) {
        $stmt->bind_param("ssss", ...$params);
    }
    $stmt->execute();
    $total_messages = $stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_messages = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_messages / $per_page);

// Get messages
$messages_sql = "SELECT * FROM messages $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
if ($params) {
    $stmt = $conn->prepare($messages_sql);
    $params_with_limit = array_merge($params, [$per_page, $offset]);
    $param_types = str_repeat('s', count($params)) . 'ii';
    $stmt->bind_param($param_types, ...$params_with_limit);
} else {
    $stmt = $conn->prepare($messages_sql);
    $stmt->bind_param("ii", $per_page, $offset);
}
$stmt->execute();
$messages = $stmt->get_result();

// Get counts for filter badges
$all_count = $conn->query("SELECT COUNT(*) as count FROM messages")->fetch_assoc()['count'];
$unread_count = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'unread'")->fetch_assoc()['count'];
$read_count = $conn->query("SELECT COUNT(*) as count FROM messages WHERE status = 'read'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Panel</title>
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
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="messages.php">Messages</a>
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
                <h2><i class="fas fa-envelope me-2"></i>Messages</h2>
                <p class="text-muted">Manage and respond to user messages.</p>
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

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="btn-group" role="group">
                    <a href="?filter=all<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        All <span class="badge bg-secondary"><?php echo $all_count; ?></span>
                    </a>
                    <a href="?filter=unread<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="btn <?php echo $filter === 'unread' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                        Unread <span class="badge bg-secondary"><?php echo $unread_count; ?></span>
                    </a>
                    <a href="?filter=read<?php echo $search ? '&search=' . urlencode($search) : ''; ?>" 
                       class="btn <?php echo $filter === 'read' ? 'btn-success' : 'btn-outline-success'; ?>">
                        Read <span class="badge bg-secondary"><?php echo $read_count; ?></span>
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <form method="GET" action="" class="d-flex">
                    <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                    <input type="text" class="form-control me-2" name="search" placeholder="Search messages..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if ($search): ?>
                        <a href="messages.php?filter=<?php echo $filter; ?>" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-times"></i>
                        </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Messages -->
        <div class="row">
            <?php if ($messages->num_rows > 0): ?>
                <?php while ($message = $messages->fetch_assoc()): ?>
                    <div class="col-12 mb-4">
                        <div class="message-card <?php echo $message['status'] === 'unread' ? 'message-unread' : 'message-read'; ?>">
                            <div class="message-header">
                                <div class="message-sender">
                                    <div class="sender-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="sender-info">
                                        <h6 class="sender-name">
                                            <?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?>
                                            <?php if ($message['status'] === 'unread'): ?>
                                                <span class="status-badge status-new">New</span>
                                            <?php else: ?>
                                                <span class="status-badge status-read">Read</span>
                                            <?php endif; ?>
                                        </h6>
                                        <div class="sender-details">
                                            <span class="contact-item">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo htmlspecialchars($message['email']); ?>
                                            </span>
                                            <span class="contact-item">
                                                <i class="fas fa-phone"></i>
                                                <?php echo htmlspecialchars($message['phone']); ?>
                                            </span>
                                            <span class="contact-item">
                                                <i class="fas fa-clock"></i>
                                                <?php echo date('M j, Y g:i A', strtotime($message['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="message-actions">
                                    <?php if ($message['status'] === 'unread'): ?>
                                        <form method="POST" action="" class="d-inline">
                                            <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                            <input type="hidden" name="action" value="mark_read">
                                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                            <button type="submit" class="action-btn action-mark-read" title="Mark as Read">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="mailto:<?php echo urlencode($message['email']); ?>?subject=Re: Your Message&body=Hello <?php echo urlencode($message['first_name']); ?>,%0D%0A%0D%0AThank you for your message. " 
                                       class="action-btn action-reply" title="Reply via Email">
                                        <i class="fas fa-reply"></i>
                                    </a>
                                    <button type="button" class="action-btn action-delete" 
                                            onclick="confirmDelete(<?php echo $message['id']; ?>)" title="Delete Message">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="message-content">
                                <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5>No messages found</h5>
                        <p>
                            <?php if ($search): ?>
                                No messages match your search criteria.
                            <?php else: ?>
                                No messages have been received yet.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>

        <!-- Stats -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="text-center text-muted">
                    Showing <?php echo ($offset + 1); ?>-<?php echo min($offset + $per_page, $total_messages); ?> 
                    of <?php echo $total_messages; ?> messages
                </div>
            </div>
        </div>
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
                    Are you sure you want to delete this message? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" class="d-inline">
                        <input type="hidden" name="message_id" id="deleteMessageId">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(messageId) {
            document.getElementById('deleteMessageId').value = messageId;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
