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
<!-- Add this right after <body> in header.php -->
<div class="main-header-wrapper">
    <!-- Existing header code here -->
    <!-- Main Header -->
    <header class="main-header">
        <?php if ($current_role === 'student'): ?>
        <!-- Back to Home button (Top-left) -->
        <div class="back-home-container">
            <a href="student_dashboard.php" class="back-home-btn">
                <i data-lucide="arrow-left"></i>
                Back to Home
            </a>
        </div>
        <?php endif; ?>

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
                
                <!-- Notification Bell -->
                <div class="notification-container">
                    <button class="notification-btn" id="notificationBtn" title="Notifications">
                        <i data-lucide="bell"></i>
                        <span class="notification-count" id="notificationCount">0</span>
                    </button>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <div class="notification-header">
                            <h4>Notifications</h4>
                            <button class="mark-all-read" id="markAllReadBtn">Mark All Read</button>
                        </div>
                        <div class="notification-list" id="notificationList">
                            <div class="no-notifications">No notifications yet.</div>
                        </div>
                    </div>
                </div>
                
                <a href="logout.php" class="logout-btn">
                    <i data-lucide="log-out" class="logout-icon"></i>
                    Logout
                </a>
            </div>
        </div>
    </header>

		<?php if ($current_role === 'librarian'): ?>
        <!-- Back to Home button (Top-left) -->
        <div class="back-home-container">
            <a href="librarian_dashboard.php" class="back-home-btn">
                <i data-lucide="arrow-left"></i>
                Back to Home
            </a>
        </div>
        <?php endif; ?>
    <!-- Navigation Bar -->
    <nav class="main-nav">
        <div class="nav-container">
            
            <?php if ($current_role === 'librarian'): ?>
				<a href="books_available_librarian.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'books_available.php') ? 'active' : ''; ?>">
                <i data-lucide="search" class="nav-icon"></i>
                Books Available
				</a>
                <a href="add_book.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'add_book.php') ? 'active' : ''; ?>">
                    <i data-lucide="plus-circle" class="nav-icon"></i>
                    Add Books
                </a>
				
				<!-- Add this in the librarian navigation section -->
				<a href="authors.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'authors.php') ? 'active' : ''; ?>">
					<i data-lucide="users" class="nav-icon"></i>
					Manage Authors
				</a>
                <a href="borrowed_books_librarian.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'borrowed_books_librarian.php') ? 'active' : ''; ?>">
                    <i data-lucide="book-check" class="nav-icon"></i>
                    Manage Borrowed
                </a>
                <a href="user_management.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'user_management.php') ? 'active' : ''; ?>">
                    <i data-lucide="users" class="nav-icon"></i>
                    User Management
                </a>
                <a href="reports.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'reports.php') ? 'active' : ''; ?>">
                    <i data-lucide="bar-chart-3" class="nav-icon"></i>
                    Reports
                </a>
            <?php else: // student ?>
			<a href="books_available.php" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'books_available.php') ? 'active' : ''; ?>">
                <i data-lucide="search" class="nav-icon"></i>
                Books Available
            </a>
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
</div>
    <!-- JavaScript for Notifications -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationDropdown = document.getElementById('notificationDropdown');
        const notificationList = document.getElementById('notificationList');
        const notificationCount = document.getElementById('notificationCount');
        const markAllReadBtn = document.getElementById('markAllReadBtn');

        // Toggle dropdown
        notificationBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('show');
            if (notificationDropdown.classList.contains('show')) {
                loadNotifications();
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            notificationDropdown.classList.remove('show');
        });

        // Mark all as read
        markAllReadBtn.addEventListener('click', function() {
            fetch('mark_notifications_read.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        loadNotifications();
                    }
                });
        });

        // Load notifications
        function loadNotifications() {
            fetch('get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notificationCount.textContent = data.unread_count;
                        notificationList.innerHTML = '';
                        if (data.notifications.length === 0) {
                            notificationList.innerHTML = '<div class="no-notifications">No notifications yet.</div>';
                        } else {
                            data.notifications.forEach(notif => {
                                const item = document.createElement('div');
                                item.className = `notification-item ${notif.status === 'unread' ? 'unread' : ''}`;
                                item.innerHTML = `
                                    <div class="notification-content">
                                        <p>${notif.message}</p>
                                        <small>${new Date(notif.date_sent).toLocaleString()}</small>
                                    </div>
                                `;
                                item.addEventListener('click', () => markAsRead(notif.notif_id));
                                notificationList.appendChild(item);
                            });
                        }
                    }
                });
        }

        // Mark single notification as read
        function markAsRead(notifId) {
            fetch('mark_notifications_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ notif_id: notifId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                }
            });
        }

        // Load on page load
        loadNotifications();
    });
    </script>
</body>
</html>