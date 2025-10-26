<?php
require_once 'includes/config.php';

// Prevent caching of this page to avoid stale content (helps UX when session changes)
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ID Card System - Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-id-card me-2"></i>ID Card System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php elseif (isset($_SESSION['admin_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/dashboard.php">Admin Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i>User
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="login.php"><i class="fas fa-sign-in-alt me-2"></i>Login</a></li>
                                <li><a class="dropdown-item" href="register.php"><i class="fas fa-user-plus me-2"></i>Register</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="admin/login.php">Admin Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1><i class="fas fa-id-card me-3"></i>Digital ID Card System</h1>
            <p class="lead">Create and manage your digital identity cards with ease</p>
            <div class="row justify-content-center mt-4">
                <div class="col-auto">
                    <a href="register.php" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-user-plus me-2"></i>Get Started
                    </a>
                    <a href="login.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col">
                    <h2>Why Choose Our ID Card System?</h2>
                    <p class="lead text-muted">Simple, secure, and reliable digital identity management</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-shield-alt fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Secure</h5>
                            <p class="card-text">Your data is protected with advanced security measures and encryption.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-bolt fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Fast</h5>
                            <p class="card-text">Generate your digital ID card instantly with our streamlined process.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Mobile Friendly</h5>
                            <p class="card-text">Access your ID card from any device, anywhere, anytime.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h3>Get in Touch</h3>
                    <p class="lead">Have questions or need help? Send us a message!</p>
                    
                    <?php if (isset($_SESSION['contact_success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo htmlspecialchars($_SESSION['contact_success']); unset($_SESSION['contact_success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['contact_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo htmlspecialchars($_SESSION['contact_error']); unset($_SESSION['contact_error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form id="contactForm" method="POST" action="process_contact.php">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contactFirstName" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="contactFirstName" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contactLastName" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="contactLastName" name="last_name" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contactPhone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" id="contactPhone" name="phone" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contactEmail" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="contactEmail" name="email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="contactMessage" class="form-label">Message *</label>
                            <textarea class="form-control" id="contactMessage" name="message" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-12 text-center">
                    <p>&copy; <?php echo date('Y'); ?> ID Card System. All rights reserved.</p>
                    <div class="social-links">
                        <a href="#" class="text-light me-3"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
