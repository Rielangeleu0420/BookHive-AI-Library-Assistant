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
$active_users = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetch_row()[0]; // Assuming all students are active users
$new_arrivals = $conn->query("SELECT COUNT(*) FROM books WHERE date_added >= CURDATE() - INTERVAL 30 DAY")->fetch_row()[0]; // Books added in last 30 days
$reservations = 0; // Placeholder for now, as no reservation system is implemented yet
$total_penalties_collected = $conn->query("SELECT SUM(amount) FROM penalties WHERE status = 'paid'")->fetch_row()[0] ?? 0;
$outstanding_penalties = $conn->query("SELECT SUM(amount) FROM penalties WHERE status = 'pending'")->fetch_row()[0] ?? 0;
$active_penalty_users = $conn->query("SELECT COUNT(DISTINCT l.student_id) FROM penalties p JOIN loans l ON p.loan_id = l.loan_id WHERE p.status = 'pending'")->fetch_row()[0] ?? 0;

// Fetch current loans for the librarian view
$current_loans = [];
$sql_loans = "SELECT l.loan_id, b.title, u.full_name AS student_name, u.user_id AS student_id, l.borrow_date, l.due_date, l.status
              FROM loans l
              JOIN books b ON l.book_id = b.book_id
              JOIN users u ON l.student_id = u.user_id
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
        // Fetch active loans and overdue books count for each student
        $student_id = $row['user_id'];
        $active_loans_count = $conn->query("SELECT COUNT(*) FROM loans WHERE student_id = $student_id AND (status = 'borrowed' OR status = 'overdue')")->fetch_row()[0];
        $overdue_books_count = $conn->query("SELECT COUNT(*) FROM loans WHERE student_id = $student_id AND status = 'overdue'")->fetch_row()[0];
        $pending_penalties_amount = $conn->query("SELECT SUM(p.amount) FROM penalties p JOIN loans l ON p.loan_id = l.loan_id WHERE l.student_id = $student_id AND p.status = 'pending'")->fetch_row()[0] ?? 0;

        $row['active_loans'] = $active_loans_count;
        $row['overdue_books'] = $overdue_books_count;
        $row['penalties'] = $pending_penalties_amount;
        $row['join_date'] = 'N/A'; // Placeholder, as join_date is not in users table in ERD

        $students[] = $row;
    }
    $result_students->free();
}

// Penalty calculation function (same as in student_dashboard)
function calculatePenalty($dueDate, $status) {
    if ($status !== 'overdue') return 0;
    $due = new DateTime($dueDate);
    $today = new DateTime();
    $diffTime = $today->getTimestamp() - $due->getTimestamp();
    $diffDays = max(0, ceil($diffTime / (1000 * 60 * 60 * 24))); // Days overdue
    return $diffDays * 100; // ₱100 per day penalty
}

?>

<?php
require_once 'header.php';

// Redirect if not a librarian
if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

$full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
?>

<link rel="stylesheet" href="librarian.css">

<div class="librarian-container">
    <!-- Header Section -->
    <div class="librarian-header">
        <div class="header-content">
            <h1 class="dashboard-title">Librarian Dashboard</h1>
            <p class="dashboard-subtitle">Manage books, users, and library operations</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <div class="welcome-header">
                    <h2 class="welcome-title">Welcome to BookHive</h2>
                    <div class="ai-subtitle">AI-Powered Library Assistant</div>
                </div>
                <p class="welcome-description">
                    Your comprehensive library management system. Manage books, users, and library operations with the power of AI assistance to enhance your workflow.
                </p>
            </div>
        </div>

        <!-- Divider -->
        <div class="section-divider"></div>

        <!-- Features Section -->
        <div class="features-section">
            <div class="feature-card">
                <h3 class="feature-title">Manage Collection</h3>
                <p class="feature-description">Add, edit, and organize your library's book collection.</p>
            </div>
            
            <div class="feature-card">
                <h3 class="feature-title">User Management</h3>
                <p class="feature-description">Monitor student accounts and loan activities.</p>
            </div>
            
            <div class="feature-card">
                <h3 class="feature-title">Analytics & Reports</h3>
                <p class="feature-description">Track usage patterns and generate insights.</p>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>
