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
    <div class="top-nav">
        <img src="logo.png" alt="Logo" class="logo">
        <div class="title-container">BookHive Inventory Management System</div>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($full_name); ?> (<?php echo ucfirst($current_role); ?>)</span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <div class="nav-bar">
        <a href="books_available.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'books_available.php') ? 'active' : ''; ?>">Books Available</a>
        <?php if ($current_role === 'librarian'): ?>
            <a href="add_book.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'add_book.php') ? 'active' : ''; ?>">Add Books</a>
            <a href="borrowed_books_librarian.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'borrowed_books_librarian.php') ? 'active' : ''; ?>">Manage Borrowed</a>
            <a href="user_management.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'user_management.php') ? 'active' : ''; ?>">User Management</a>
            <!-- Add more librarian links here -->
        <?php else: // student ?>
            <a href="borrow_book.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'borrow_book.php') ? 'active' : ''; ?>">Borrow a Book</a>
            <a href="my_loans.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'my_loans.php') ? 'active' : ''; ?>">My Loans</a>
            <!-- Add more student links here -->
        <?php endif; ?>
        <a href="about.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : ''; ?>">About</a>
    </div>

    <div class="main-content">
        <!-- Content will be inserted here -->