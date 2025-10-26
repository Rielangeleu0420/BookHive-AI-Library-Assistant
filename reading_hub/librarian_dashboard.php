<?php
require_once 'header.php';

// Redirect if not a librarian
if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? $_SESSION['username'];

// Fetch summary data
$total_books = $conn->query("SELECT COUNT(*) FROM books")->fetch_row()[0];
$borrowed_books = $conn->query("SELECT COUNT(*) FROM loans WHERE status = 'borrowed'")->fetch_row()[0];
$overdue_books = $conn->query("SELECT COUNT(*) FROM loans WHERE status = 'overdue'")->fetch_row()[0];
$active_users = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0];
$new_arrivals = $conn->query("SELECT COUNT(*) FROM books WHERE date_added >= CURDATE() - INTERVAL 30 DAY")->fetch_row()[0];
$reservations = 0; // Placeholder for now
$total_penalties_collected = $conn->query("SELECT SUM(amount) FROM penalties WHERE status = 'paid'")->fetch_row()[0] ?? 0;
$outstanding_penalties = $conn->query("SELECT SUM(amount) FROM penalties WHERE status = 'pending'")->fetch_row()[0] ?? 0;

// FIXED: Changed l.student_id to l.user_id
$active_penalty_users = $conn->query("SELECT COUNT(DISTINCT l.user_id) FROM penalties p JOIN loans l ON p.loan_id = l.loan_id WHERE p.status = 'pending'")->fetch_row()[0] ?? 0;

// Fetch current loans for the librarian view - FIXED: Changed student_id to user_id
$current_loans = [];
$sql_loans = "SELECT l.loan_id, b.title, u.full_name AS student_name, u.user_id AS student_id, l.borrow_date, l.due_date, l.status
              FROM loans l
              JOIN books b ON l.book_id = b.book_id
              JOIN users u ON l.user_id = u.user_id
              WHERE l.status = 'borrowed' OR l.status = 'overdue'
              ORDER BY l.due_date ASC";
if ($result_loans = $conn->query($sql_loans)) {
    while ($row = $result_loans->fetch_assoc()) {
        $row['is_overdue'] = (new DateTime($row['due_date']) < new DateTime() && $row['status'] !== 'returned');
        if ($row['is_overdue']) {
            $row['status'] = 'overdue';
        }
        $current_loans[] = $row;
    }
    $result_loans->free();
}

// Fetch authors
$authors = [];
$sql_authors = "SELECT a.author_id, a.author_name, a.biography, COUNT(b.book_id) AS books_count
                FROM authors a
                LEFT JOIN books b ON a.author_id = b.author_id
                GROUP BY a.author_id
                ORDER BY a.author_name ASC";
if ($result_authors = $conn->query($sql_authors)) {
    while ($row = $result_authors->fetch_assoc()) {
        $authors[] = $row;
    }
    $result_authors->free();
}

// Fetch students (users with role 'student')
$students = [];
$sql_students = "SELECT user_id, full_name, email, lrn, year_level FROM users WHERE role = 'student' ORDER BY full_name ASC";
if ($result_students = $conn->query($sql_students)) {
    while ($row = $result_students->fetch_assoc()) {
        // Fetch active loans and overdue books count for each student - FIXED: Changed student_id to user_id
        $student_id = $row['user_id'];
        $active_loans_count = $conn->query("SELECT COUNT(*) FROM loans WHERE user_id = $student_id AND (status = 'borrowed' OR status = 'overdue')")->fetch_row()[0];
        $overdue_books_count = $conn->query("SELECT COUNT(*) FROM loans WHERE user_id = $student_id AND status = 'overdue'")->fetch_row()[0];
        
        // FIXED: Changed l.student_id to l.user_id
        $pending_penalties_amount = $conn->query("SELECT SUM(p.amount) FROM penalties p JOIN loans l ON p.loan_id = l.loan_id WHERE l.user_id = $student_id AND p.status = 'pending'")->fetch_row()[0] ?? 0;

        $row['active_loans'] = $active_loans_count;
        $row['overdue_books'] = $overdue_books_count;
        $row['penalties'] = $pending_penalties_amount;
        $row['join_date'] = 'N/A';

        $students[] = $row;
    }
    $result_students->free();
}

// Penalty calculation function
function calculatePenalty($dueDate, $status) {
    if ($status !== 'overdue') return 0;
    $due = new DateTime($dueDate);
    $today = new DateTime();
    $diffTime = $today->getTimestamp() - $due->getTimestamp();
    $diffDays = max(0, ceil($diffTime / (1000 * 60 * 60 * 24)));
    return $diffDays * 100; // ₱100 per day penalty
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Librarian Dashboard - BookHive</title>
</head>
<body class="min-h-screen bg-background">
    <div class="dashboard-container">
        <!-- Main Content -->
        <main class="p-6 space-y-6">
            <!-- Welcome Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-primary mb-2">Welcome back, <?php echo htmlspecialchars(explode(' ', $full_name)[0]); ?>! 📚</h1>
                    <p class="text-secondary text-lg">
                        Manage your library with AI-powered assistance and insights
                    </p>
                </div>
                <a href="manage_books.php" class="btn btn-info">
                    <i data-lucide="book-plus" class="w-4 h-4 mr-2"></i>
                    Add New Book
                </a>
            </div>

            <!-- Quick Stats -->
            <div class="grid gap-6 md:grid-cols-6">
                <!-- Total Books Card -->
                <div class="card stat-card-1">
                    <div class="card-header">
                        <div class="card-title">Total Books</div>
                        <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
                            <i data-lucide="book-open" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-primary mb-1"><?php echo $total_books; ?></div>
                        <p class="text-sm text-secondary">In library collection</p>
                    </div>
                </div>
                
                <!-- Borrowed Books Card -->
                <div class="card stat-card-2">
                    <div class="card-header">
                        <div class="card-title">Borrowed Books</div>
                        <div class="w-10 h-10 bg-secondary rounded-xl flex items-center justify-center">
                            <i data-lucide="book-marked" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-primary mb-1"><?php echo $borrowed_books; ?></div>
                        <p class="text-sm text-secondary">Currently checked out</p>
                    </div>
                </div>
                
                <!-- Overdue Books Card -->
                <div class="card stat-card-4">
                    <div class="card-header">
                        <div class="card-title">Overdue Books</div>
                        <div class="w-10 h-10 bg-danger rounded-xl flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-danger mb-1"><?php echo $overdue_books; ?></div>
                        <p class="text-sm text-secondary">Past return date</p>
                    </div>
                </div>
                
                <!-- Active Students Card -->
                <div class="card stat-card-3">
                    <div class="card-header">
                        <div class="card-title">Active Students</div>
                        <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center">
                            <i data-lucide="users" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-primary mb-1"><?php echo $active_users; ?></div>
                        <p class="text-sm text-secondary">Registered users</p>
                    </div>
                </div>
                
                <!-- New Arrivals Card -->
                <div class="card stat-card-5">
                    <div class="card-header">
                        <div class="card-title">New Arrivals</div>
                        <div class="w-10 h-10 bg-success rounded-xl flex items-center justify-center">
                            <i data-lucide="package" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-primary mb-1"><?php echo $new_arrivals; ?></div>
                        <p class="text-sm text-secondary">Last 30 days</p>
                    </div>
                </div>
                
                <!-- Penalties Collected Card -->
                <div class="card stat-card-2">
                    <div class="card-header">
                        <div class="card-title">Penalties Collected</div>
                        <div class="w-10 h-10 bg-warning rounded-xl flex items-center justify-center">
                            <span class="text-white font-bold text-lg">₱</span>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-warning mb-1">
                            ₱<?php echo number_format($total_penalties_collected, 2); ?>
                        </div>
                        <p class="text-sm text-secondary">Total collected fines</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="section-card quick-actions">
                <div class="card-header bg-gradient-to-r">
                    <div class="card-title text-xl flex items-center">
                        <i data-lucide="zap" class="w-5 h-5 mr-2"></i>
                        Quick Actions
                    </div>
                    <div class="card-description">Common library management tasks</div>
                </div>
                <div class="card-content">
                    <div class="action-buttons">
                        <a href="add_book.php" class="action-btn action-btn-search">
                            <i data-lucide="book-plus" class="w-4 h-4"></i>
                            Add New Book
                        </a>
                        <a href="user_management.php" class="action-btn action-btn-ai">
                            <i data-lucide="user-plus" class="w-4 h-4"></i>
                            Manage Users
                        </a>
                        <a href="authors.php" class="action-btn action-btn-loans">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            Manage Authors
                        </a>
                        <a href="reports.php" class="action-btn action-btn-history">
                            <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                            View Reports
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 md:grid-cols-2">
                <!-- Recent Loans Section -->
                <div class="section-card">
                    <div class="card-header bg-gradient-to-r">
                        <div class="card-title text-xl flex items-center">
                            <i data-lucide="book-marked" class="w-5 h-5 mr-2"></i>
                            Recent Loans
                        </div>
                        <div class="card-description">Currently borrowed books</div>
                    </div>
                    <div class="card-content">
                        <div class="space-y-4">
                            <?php if (!empty($current_loans)): ?>
                                <?php foreach (array_slice($current_loans, 0, 5) as $loan): ?>
                                    <div class="book-item">
                                        <div class="book-cover">
                                            <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                                        </div>
                                        <div class="book-info">
                                            <div class="book-title"><?php echo htmlspecialchars($loan['title']); ?></div>
                                            <div class="book-author"><?php echo htmlspecialchars($loan['student_name']); ?></div>
                                            <div class="book-meta">
                                                <div class="meta-item">
                                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                                    <span>Due: <?php echo htmlspecialchars($loan['due_date']); ?></span>
                                                </div>
                                                <?php if ($loan['status'] === 'overdue'): ?>
                                                    <span class="status-badge badge-overdue">Overdue</span>
                                                <?php else: ?>
                                                    <span class="status-badge badge-available">Borrowed</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted-foreground py-4">
                                    No active loans
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Top Authors Section -->
                <div class="section-card">
                    <div class="card-header bg-gradient-to-r">
                        <div class="card-title text-xl flex items-center">
                            <i data-lucide="user" class="w-5 h-5 mr-2"></i>
                            Top Authors
                        </div>
                        <div class="card-description">Most published authors</div>
                    </div>
                    <div class="card-content">
                        <div class="space-y-4">
                            <?php if (!empty($authors)): ?>
                                <?php foreach (array_slice($authors, 0, 5) as $author): ?>
                                    <div class="book-item">
                                        <div class="book-cover">
                                            <i data-lucide="user" class="w-6 h-6 text-white"></i>
                                        </div>
                                        <div class="book-info">
                                            <div class="book-title"><?php echo htmlspecialchars($author['author_name']); ?></div>
                                            <div class="book-author">Author</div>
                                            <div class="book-meta">
                                                <span class="status-badge badge-category">
                                                    <?php echo $author['books_count']; ?> books
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted-foreground py-4">
                                    No authors found
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Library Features Section -->
            <div class="section-card">
                <div class="card-header bg-gradient-to-r">
                    <div class="card-title text-xl flex items-center">
                        <i data-lucide="settings" class="w-5 h-5 mr-2"></i>
                        Library Management Features
                    </div>
                    <div class="card-description">Everything you need to manage your library efficiently</div>
                </div>
                <div class="card-content">
                    <div class="grid md:grid-cols-3 gap-6">
                        <div class="feature-card text-center p-6 rounded-lg border border-border hover:shadow-lg transition-all duration-300">
                            <div class="w-16 h-16 bg-primary/10 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                                <i data-lucide="book-open" class="w-8 h-8 text-primary"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Manage Collection</h3>
                            <p class="text-foreground/70">Add, edit, and organize your library's book collection.</p>
                        </div>
                        
                        <div class="feature-card text-center p-6 rounded-lg border border-border hover:shadow-lg transition-all duration-300">
                            <div class="w-16 h-16 bg-secondary/10 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                                <i data-lucide="users" class="w-8 h-8 text-secondary"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">User Management</h3>
                            <p class="text-foreground/70">Monitor student accounts and loan activities.</p>
                        </div>
                        
                        <div class="feature-card text-center p-6 rounded-lg border border-border hover:shadow-lg transition-all duration-300">
                            <div class="w-16 h-16 bg-accent/10 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                                <i data-lucide="bar-chart-3" class="w-8 h-8 text-accent"></i>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Analytics & Reports</h3>
                            <p class="text-foreground/70">Track usage patterns and generate insights.</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>

<?php
require_once 'footer.php';
?>