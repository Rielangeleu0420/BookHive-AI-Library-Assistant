<?php
require_once 'functions.php';

// Check if the user is logged in, if not then redirect to login page
if (!isLoggedIn()) {
    redirectToLogin();
}

$current_role = getUserRole();
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'] ?? $username; // Get full name if available

// Check if the AI Chatbot modal should be open
$showAIChat = isset($_GET['show_ai_chat']) && $_GET['show_ai_chat'] === 'true';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BookHive - <?php echo ucfirst($current_role); ?> Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Bungee&display=swap" rel="stylesheet">
    <!-- Add Lucide Icons for better UI -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</head>
<body>
    <!-- Main Header -->
    <header class="main-header">
        <div class="header-container">
            <!-- Logo Section -->
            <div class="logo-section">
                <div class="logo-icon">
                    <i data-lucide="book-open" class="logo-book-icon"></i>
                </div>
                <div class="logo-text">
                    <span class="logo-primary">Book</span>
                    <span class="logo-secondary">Hive</span>
                </div>
            </div>

            <!-- Page Title -->
            <div class="page-title">
                <?php echo ucfirst($current_role); ?> Dashboard
            </div>

            <!-- User Info Section -->
            <div class="user-section">
                <div class="user-info">
                    <span class="user-welcome">Welcome, <?php echo htmlspecialchars(explode(' ', $full_name)[0]); ?></span>
                    <div class="user-role-badge">
                        <?php echo ucfirst($current_role); ?>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i data-lucide="log-out" class="logout-icon"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

    <!-- Navigation Bar -->
    <nav class="main-nav">
        <div class="nav-container">
            <a href="books_available.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'books_available.php') ? 'active' : ''; ?>">
                <i data-lucide="search" class="nav-icon"></i>
                Books Available
            </a>
            <?php if ($current_role === 'librarian'): ?>
                <a href="add_book.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add_book.php') ? 'active' : ''; ?>">
                    <i data-lucide="plus-circle" class="nav-icon"></i>
                    Add Books
                </a>
                <a href="borrowed_books_librarian.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'borrowed_books_librarian.php') ? 'active' : ''; ?>">
                    <i data-lucide="book-check" class="nav-icon"></i>
                    Manage Borrowed
                </a>
                <a href="user_management.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'user_management.php') ? 'active' : ''; ?>">
                    <i data-lucide="users" class="nav-icon"></i>
                    User Management
                </a>
            <?php else: // student ?>
                <a href="borrow_book.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'borrow_book.php') ? 'active' : ''; ?>">
                    <i data-lucide="book-up" class="nav-icon"></i>
                    Borrow a Book
                </a>
                <a href="my_loans.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'my_loans.php') ? 'active' : ''; ?>">
                    <i data-lucide="book-marked" class="nav-icon"></i>
                    My Loans
                </a>
            <?php endif; ?>
            <a href="about.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>">
                <i data-lucide="info" class="nav-icon"></i>
                About
            </a>
        </div>
    </nav>
