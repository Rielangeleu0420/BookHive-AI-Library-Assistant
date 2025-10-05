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

<div class="min-h-screen bg-background">
    <!-- Header (already included by header.php) -->

    <!-- Main Content -->
    <main class="p-6">
        <div class="mb-6">
            <h1>Librarian Dashboard</h1>
            <p class="text-muted-foreground">
                Manage books, users, and library operations
            </p>
        </div>

        <div class="tabs-container">
            <div class="tabs-list">
                <button class="tab-trigger active" onclick="openTab(event, 'home')">Home</button>
                <button class="tab-trigger" onclick="openTab(event, 'overview')">Overview</button>
                <button class="tab-trigger" onclick="openTab(event, 'loans')">Current Loans</button>
                <button class="tab-trigger" onclick="openTab(event, 'books')">Books</button>
                <button class="tab-trigger" onclick="openTab(event, 'authors')">Authors</button>
                <button class="tab-trigger" onclick="openTab(event, 'users')">Users</button>
                <button class="tab-trigger" onclick="openTab(event, 'reports')">Reports</button>
            </div>

            <!-- Home Tab -->
            <div id="home" class="tab-content active space-y-6">
                <div class="text-center py-16">
                    <div class="flex items-center justify-center space-x-3 mb-6">
                        <div class="w-16 h-16 bg-[var(--secondary)] rounded-2xl flex items-center justify-center shadow-xl">
                            <i data-lucide="book-open" class="w-8 h-8 text-white"></i>
                        </div>
                        <div class="text-left">
                            <h1 class="text-3xl font-bold text-foreground">Welcome to BookHive</h1>
                            <p class="text-foreground/70 text-lg">AI-Powered Library Assistant</p>
                        </div>
                    </div>
                    <p class="text-xl text-foreground/80 mb-10 max-w-3xl mx-auto leading-relaxed">
                        Your comprehensive library management system. Manage books, users, and library operations 
                        with the power of AI assistance to enhance your workflow.
                    </p>
                    <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
                        <div class="card border-0 shadow-lg bg-gradient-to-br from-[var(--primary)]/10 to-[var(--primary)]/5">
                            <div class="card-content pt-6 text-center">
                                <i data-lucide="book-open" class="w-12 h-12 text-[var(--primary)] mx-auto mb-4"></i>
                                <h3 class="font-semibold text-foreground mb-2">Manage Collection</h3>
                                <p class="text-sm text-foreground/70">Add, edit, and organize your library's book collection</p>
                            </div>
                        </div>
                        <div class="card border-0 shadow-lg bg-gradient-to-br from-[var(--secondary)]/10 to-[var(--secondary)]/5">
                            <div class="card-content pt-6 text-center">
                                <i data-lucide="users" class="w-12 h-12 text-[var(--secondary)] mx-auto mb-4"></i>
                                <h3 class="font-semibold text-foreground mb-2">User Management</h3>
                                <p class="text-sm text-foreground/70">Monitor student accounts and loan activities</p>
                            </div>
                        </div>
                        <div class="card border-0 shadow-lg bg-gradient-to-br from-[var(--accent)]/10 to-[var(--accent)]/5">
                            <div class="card-content pt-6 text-center">
                                <i data-lucide="trending-up" class="w-12 h-12 text-[var(--accent)] mx-auto mb-4"></i>
                                <h3 class="font-semibold text-foreground mb-2">Analytics & Reports</h3>
                                <p class="text-sm text-foreground/70">Track usage patterns and generate insights</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overview Tab -->
            <div id="overview" class="tab-content space-y-6">
                <!-- Stats Cards -->
                <div class="grid gap-4 md:grid-cols-4 lg:grid-cols-8">
                    <div class="card">
                        <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="card-title text-sm font-medium">Total Books</div>
                            <i data-lucide="book-open" class="h-4 w-4 text-muted-foreground"></i>
                        </div>
                        <div class="card-content">
                            <div class="text-2xl font-bold"><?php echo number_format($total_books); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="card-title text-sm font-medium">Borrowed</div>
                            <i data-lucide="trending-up" class="h-4 w-4 text-muted-foreground"></i>
                        </div>
                        <div class="card-content">
                            <div class="text-2xl font-bold"><?php echo number_format($borrowed_books); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="card-title text-sm font-medium">Overdue</div>
                            <i data-lucide="alert-triangle" class="h-4 w-4 text-muted-foreground"></i>
                        </div>
                        <div class="card-content">
                            <div class="text-2xl font-bold text-red-600"><?php echo number_format($overdue_books); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="card-title text-sm font-medium">Active Users</div>
                            <i data-lucide="users" class="h-4 w-4 text-muted-foreground"></i>
                        </div>
                        <div class="card-content">
                            <div class="text-2xl font-bold"><?php echo number_format($active_users); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="card-title text-sm font-medium">New Arrivals</div>
                            <i data-lucide="plus" class="h-4 w-4 text-muted-foreground"></i>
                        </div>
                        <div class="card-content">
                            <div class="text-2xl font-bold"><?php echo number_format($new_arrivals); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="card-title text-sm font-medium">Reservations</div>
                            <i data-lucide="calendar" class="h-4 w-4 text-muted-foreground"></i>
                        </div>
                        <div class="card-content">
                            <div class="text-2xl font-bold"><?php echo number_format($reservations); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="card-title text-sm font-medium">Total Penalties</div>
                            <span class="h-4 w-4 text-red-600 font-bold">₱</span>
                        </div>
                        <div class="card-content">
                            <div class="text-2xl font-bold text-red-600">₱<?php echo number_format($outstanding_penalties, 2); ?></div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="card-title text-sm font-medium">Users with Fines</div>
                            <i data-lucide="alert-triangle" class="h-4 w-4 text-red-600"></i>
                        </div>
                        <div class="card-content">
                            <div class="text-2xl font-bold text-red-600"><?php echo number_format($active_penalty_users); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Quick Actions</div>
                    </div>
                    <div class="card-content">
                        <div class="grid gap-2 md:grid-cols-4">
                            <button class="btn btn-primary" onclick="window.location.href='add_book.php'">
                                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                                Add Book
                            </button>
                            <button class="btn btn-secondary" onclick="openModal('addAuthorModal')">
                                <i data-lucide="user-plus" class="w-4 h-4 mr-2"></i>
                                Add Author
                            </button>
                            <button class="btn btn-secondary" onclick="openModal('addUserModal')">
                                <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                                Add User
                            </button>
                            <button class="btn btn-danger" onclick="window.location.href='borrowed_books_librarian.php?status=overdue'">
                                <i data-lucide="alert-triangle" class="w-4 h-4 mr-2"></i>
                                View Overdue
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Loans Tab -->
            <div id="loans" class="tab-content space-y-4">
                <div class="flex items-center justify-between">
                    <h2>Current Loans</h2>
                    <div class="flex items-center space-x-2">
                        <input type="text" placeholder="Search loans..." class="form-control w-80" />
                        <button class="btn btn-secondary btn-sm">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-content p-0">
                        <div class="space-y-0">
                            <?php if (!empty($current_loans)): ?>
                                <?php foreach ($current_loans as $loan): ?>
                                    <?php $penalty = calculatePenalty($loan['due_date'], $loan['status']); ?>
                                    <div class="p-4 flex items-center justify-between border-b">
                                        <div class="flex items-center space-x-4">
                                            <div>
                                                <h4 class="font-medium"><?php echo htmlspecialchars($loan['title']); ?></h4>
                                                <p class="text-sm text-muted-foreground">
                                                    <?php echo htmlspecialchars($loan['student_name']); ?> (<?php echo htmlspecialchars($loan['student_id']); ?>)
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <div class="text-right">
                                                <p class="text-sm">Due: <?php echo htmlspecialchars($loan['due_date']); ?></p>
                                                <p class="text-xs text-muted-foreground">Borrowed: <?php echo htmlspecialchars($loan['borrow_date']); ?></p>
                                                <?php if ($penalty > 0): ?>
                                                    <p class="text-sm text-red-600 font-medium">Fine: ₱<?php echo number_format($penalty, 2); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge <?php echo ($loan['status'] === 'overdue' ? 'bg-red-600 text-white' : 'bg-green-600 text-white'); ?>">
                                                <?php echo htmlspecialchars(ucfirst($loan['status'])); ?>
                                            </span>
                                            <div class="dropdown">
                                                <button class="btn btn-ghost btn-sm dropdown-toggle">
                                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="#" class="dropdown-item" onclick="extendDueDate(<?php echo $loan['loan_id']; ?>, '<?php echo $loan['due_date']; ?>')">Extend Due Date</a>
                                                    <a href="#" class="dropdown-item">Send Reminder</a>
                                                    <a href="#" class="dropdown-item" onclick="returnBook(<?php echo $loan['loan_id']; ?>)">Mark as Returned</a>
                                                    <?php if ($penalty > 0): ?>
                                                        <a href="#" class="dropdown-item">Waive Fine</a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted-foreground py-4">No current loans.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Books Tab -->
            <div id="books" class="tab-content space-y-4">
                <div class="flex items-center justify-between">
                    <h2>Books</h2>
                    <button class="btn btn-primary" onclick="window.location.href='add_book.php'">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Add Book
                    </button>
                </div>

                <div class="card">
                    <div class="card-content p-4">
                        <p class="text-muted-foreground">
                            List of books will be displayed here (with options to edit, delete, or view details).
                        </p>
                        <!-- Example: Link to books_available.php for actual list -->
                        <a href="books_available.php" class="btn btn-info mt-4">View All Books</a>
                    </div>
                </div>
            </div>

            <!-- Authors Tab -->
            <div id="authors" class="tab-content space-y-4">
                <div class="flex items-center justify-between">
                    <h2>Authors</h2>
                    <button class="btn btn-primary" onclick="openModal('addAuthorModal')">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Add Author
                    </button>
                </div>

                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <?php if (!empty($authors)): ?>
                        <?php foreach ($authors as $author): ?>
                            <div class="card">
                                <div class="card-header">
                                    <div class="flex items-center justify-between">
                                        <div class="card-title text-lg"><?php echo htmlspecialchars($author['author_name']); ?></div>
                                        <div class="dropdown">
                                            <button class="btn btn-ghost btn-sm dropdown-toggle">
                                                <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                <a href="#" class="dropdown-item">
                                                    <i data-lucide="edit" class="mr-2 h-4 w-4"></i>
                                                    Edit
                                                </a>
                                                <a href="#" class="dropdown-item text-red-600">
                                                    <i data-lucide="trash-2" class="mr-2 h-4 w-4"></i>
                                                    Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-content">
                                    <p class="text-sm text-muted-foreground mb-2"><?php echo htmlspecialchars($author['biography'] ?? 'N/A'); ?></p>
                                    <p class="text-sm"><?php echo htmlspecialchars($author['books_count']); ?> books in library</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted-foreground">No authors found.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Users Tab -->
            <div id="users" class="tab-content space-y-4">
                <div class="flex items-center justify-between">
                    <h2>User Management</h2>
                    <button class="btn btn-primary" onclick="openModal('addUserModal')">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        Add User
                    </button>
                </div>

                <div class="card">
                    <div class="card-content p-0">
                        <div class="space-y-0">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <div class="p-4 flex items-center justify-between border-b">
                                        <div>
                                            <h4 class="font-medium"><?php echo htmlspecialchars($student['full_name']); ?></h4>
                                            <p class="text-sm text-muted-foreground"><?php echo htmlspecialchars($student['email']); ?></p>
                                            <p class="text-xs text-muted-foreground">ID: <?php echo htmlspecialchars($student['user_id']); ?> • LRN: <?php echo htmlspecialchars($student['lrn'] ?? 'N/A'); ?> • Year Level: <?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></p>
                                        </div>
                                        <div class="flex items-center space-x-4">
                                            <div class="text-right">
                                                <p class="text-sm">Active loans: <?php echo htmlspecialchars($student['active_loans']); ?></p>
                                                <p class="text-xs text-muted-foreground">
                                                    Overdue: <?php echo htmlspecialchars($student['overdue_books']); ?>
                                                </p>
                                                <?php if ($student['penalties'] > 0): ?>
                                                    <p class="text-sm text-red-600 font-medium">
                                                        Fines: ₱<?php echo number_format($student['penalties'], 2); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-ghost btn-sm dropdown-toggle">
                                                    <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a href="#" class="dropdown-item">View Details</a>
                                                    <a href="#" class="dropdown-item">Send Message</a>
                                                    <a href="#" class="dropdown-item text-red-600">Suspend Account</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted-foreground py-4">No students found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Tab -->
            <div id="reports" class="tab-content space-y-4">
                <h2>Reports & Analytics</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Popular Books</div>
                            <div class="card-description">Most borrowed books this month</div>
                        </div>
                        <div class="card-content">
                            <p class="text-muted-foreground text-center py-8">
                                Reports and analytics would be displayed here.
                            </p>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Usage Statistics</div>
                            <div class="card-description">Library usage trends</div>
                        </div>
                        <div class="card-content">
                            <p class="text-muted-foreground text-center py-8">
                                Usage charts and statistics would be displayed here.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modals (Add Book, Add Author, Add User) -->
    <div id="addBookModal" class="ai-chat-modal" style="display: none;">
        <div class="ai-chat-content">
            <div class="ai-chat-header">
                <i data-lucide="book-open" class="ai-chat-icon"></i>
                <span class="ai-chat-title">Add New Book</span>
                <button class="ai-chat-close-btn" onclick="closeModal('addBookModal')">&times;</button>
            </div>
            <div class="ai-chat-body">
                <form action="add_book.php" method="post" class="space-y-4">
                    <div class="form-group">
                        <label for="title">Title</label>
                        <input type="text" id="title" name="title" placeholder="Enter book title" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="author_id">Author</label>
                        <select id="author_id" name="author_id" class="form-control" required>
                            <option value="">Select author</option>
                            <?php foreach ($authors as $author): ?>
                                <option value="<?php echo $author['author_id']; ?>"><?php echo htmlspecialchars($author['author_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="genre_id">Category</label>
                        <select id="genre_id" name="genre_id" class="form-control" required>
                            <option value="">Select category</option>
                            <?php
                            $genres = $conn->query("SELECT genre_id, genre_name FROM genres ORDER BY genre_name")->fetch_all(MYSQLI_ASSOC);
                            foreach ($genres as $genre): ?>
                                <option value="<?php echo $genre['genre_id']; ?>"><?php echo htmlspecialchars($genre['genre_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="quantity_total">Quantity</label>
                        <input type="number" id="quantity_total" name="quantity_total" min="1" placeholder="1" class="form-control" required />
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addBookModal')">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">Add Book</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="addAuthorModal" class="ai-chat-modal" style="display: none;">
        <div class="ai-chat-content">
            <div class="ai-chat-header">
                <i data-lucide="user-plus" class="ai-chat-icon"></i>
                <span class="ai-chat-title">Add New Author</span>
                <button class="ai-chat-close-btn" onclick="closeModal('addAuthorModal')">&times;</button>
            </div>
            <div class="ai-chat-body">
                <form action="#" method="post" class="space-y-4">
                    <div class="form-group">
                        <label for="authorName">Author Name</label>
                        <input type="text" id="authorName" name="authorName" placeholder="Enter author name" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="specialization">Specialization</label>
                        <input type="text" id="specialization" name="specialization" placeholder="Enter specialization" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label for="bio">Biography</label>
                        <textarea id="bio" name="bio" placeholder="Enter author biography" class="form-control"></textarea>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addAuthorModal')">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">Add Author</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="addUserModal" class="ai-chat-modal" style="display: none;">
        <div class="ai-chat-content">
            <div class="ai-chat-header">
                <i data-lucide="users" class="ai-chat-icon"></i>
                <span class="ai-chat-title">Add New User</span>
                <button class="ai-chat-close-btn" onclick="closeModal('addUserModal')">&times;</button>
            </div>
            <div class="ai-chat-body">
                <form action="signup.php" method="post" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" placeholder="John Doe" class="form-control" required />
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="johndoe" class="form-control" required />
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="user@university.edu" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control" required>
                            <option value="student">Student</option>
                            <option value="librarian">Librarian</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-trigger");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Set default active tab on load
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.tab-trigger').click();
        });

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Functions for loan actions (return, extend, penalty) - these would interact with process_loan.php
        async function returnBook(loanId) {
            if (confirm('Are you sure you want to mark this book as returned?')) {
                const response = await fetch('process_loan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'return', loan_id: loanId })
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) {
                    location.reload();
                }
            }
        }

        async function extendDueDate(loanId, currentDueDate) {
            const newDueDate = prompt(`Enter new due date (YYYY-MM-DD) for loan ID ${loanId}. Current: ${currentDueDate}`);
            if (newDueDate) {
                const response = await fetch('process_loan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'extend', loan_id: loanId, new_due_date: newDueDate })
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) {
                    location.reload();
                }
            }
        }

        async function assessPenalty(loanId) {
            const penaltyAmount = prompt(`Enter penalty amount for loan ID ${loanId}:`);
            if (penaltyAmount && !isNaN(penaltyAmount) && parseFloat(penaltyAmount) > 0) {
                const response = await fetch('process_loan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'assess_penalty', loan_id: loanId, amount: parseFloat(penaltyAmount) })
                });
                const result = await response.json();
                alert(result.message);
                if (result.success) {
                    location.reload();
                }
            } else if (penaltyAmount !== null) {
                alert('Please enter a valid positive number for the penalty amount.');
            }
        }
    </script>
</div>

<?php
require_once 'footer.php';
?>