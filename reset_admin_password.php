<?php
/**
 * Admin Password Reset Script
 * Use this to reset the admin password
 * DELETE THIS FILE after use for security!
 */

require_once 'includes/config.php';

// Set your new admin password here
$new_password = 'admin123'; // Change this to your desired password
$username = 'admin';

// Hash the password
$hashed_password = secure_hash_password($new_password);

// Update the admin password in database
$stmt = $conn->prepare("UPDATE admin SET password = ? WHERE username = ?");
$stmt->bind_param("ss", $hashed_password, $username);

if ($stmt->execute()) {
    echo "<h2 style='color: green;'>✅ Admin password has been reset successfully!</h2>";
    echo "<p><strong>Username:</strong> admin</p>";
    echo "<p><strong>Password:</strong> " . htmlspecialchars($new_password) . "</p>";
    echo "<p style='color: red;'><strong>IMPORTANT:</strong> Please delete this file (reset_admin_password.php) immediately for security reasons!</p>";
    echo "<p><a href='admin/login.php'>Go to Admin Login</a></p>";
} else {
    echo "<h2 style='color: red;'>❌ Error updating password: " . $conn->error . "</h2>";
}

$stmt->close();
$conn->close();
?>
