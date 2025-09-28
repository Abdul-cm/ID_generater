<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['contact_error'] = 'Security token validation failed. Please try again.';
        header('Location: index.php#contact');
        exit();
    }
    
    $first_name = sanitize_input($_POST['first_name']);
    $last_name = sanitize_input($_POST['last_name']);
    $phone = sanitize_input($_POST['phone']);
    $email = sanitize_input($_POST['email']);
    $message = sanitize_input($_POST['message']);
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($email) || empty($message)) {
        $_SESSION['contact_error'] = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['contact_error'] = 'Invalid email format.';
    } else {
        // Insert message into database
        $stmt = $conn->prepare("INSERT INTO messages (first_name, last_name, phone, email, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $phone, $email, $message);
        
        if ($stmt->execute()) {
            $_SESSION['contact_success'] = 'Thank you for your message! We will get back to you soon.';
        } else {
            $_SESSION['contact_error'] = 'Failed to send message. Please try again.';
        }
    }
} else {
    $_SESSION['contact_error'] = 'Invalid request method.';
}

// Redirect back to home page
header('Location: index.php#contact');
exit();
?>
