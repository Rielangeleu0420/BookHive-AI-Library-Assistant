<?php
require_once 'header.php';

// Redirect if not a student
if (getUserRole() !== 'student') {
    header("Location: librarian_dashboard.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? $_SESSION['username'];

// Fetch current loans for the student
$current_loans = [];
$sql_loans = "SELECT l.loan_id, b.title, a.author_name, l.due_date, l.status
              FROM loans l
              JOIN books b ON l.book_id = b.book_id
              LEFT JOIN authors a ON b.author_id = a.author_id
              WHERE l.student_id = ? AND (l.status = 'borrowed' OR l.status = 'overdue')
              ORDER BY l.due_date ASC";
if ($stmt_loans = $conn->prepare($sql_loans)) {
    $stmt_loans->bind_param("i", $student_id);
    $stmt_loans->execute();
    $result_loans = $stmt_loans->get_result();
    while ($row = $result_loans->fetch_assoc()) {
        $current_loans[] = $row;
    }
    $stmt_loans->close();
}

// Calculate penalties
function calculatePenalty($dueDate, $status) {
    if ($status !== 'overdue') return 0;
    $due = new DateTime($dueDate);
    $today = new DateTime();
    $diffTime = $today->getTimestamp() - $due->getTimestamp();
    $diffDays = max(0, ceil($diffTime / (1000 * 60 * 60 * 24))); // Days overdue
    return $diffDays * 100; // ₱100 per day penalty
}

$total_penalties = 0;
foreach ($current_loans as &$loan) {
    $loan['is_overdue'] = (new DateTime($loan['due_date']) < new DateTime() && $loan['status'] !== 'returned');
    if ($loan['is_overdue']) {
        $loan['status'] = 'overdue'; // Ensure status is 'overdue' if it is
    }
    $loan['penalty_amount'] = calculatePenalty($loan['due_date'], $loan['status']);
    $total_penalties += $loan['penalty_amount'];
}

// Fetch borrowing history (returned books)
$borrowing_history = [];
$sql_history = "SELECT l.loan_id, b.title, a.author_name, l.borrow_date, l.return_date
                FROM loans l
                JOIN books b ON l.book_id = b.book_id
                LEFT JOIN authors a ON b.author_id = a.author_id
                WHERE l.student_id = ? AND l.status = 'returned'
                ORDER BY l.return_date DESC";
if ($stmt_history = $conn->prepare($sql_history)) {
    $stmt_history->bind_param("i", $student_id);
    $stmt_history->execute();
    $result_history = $stmt_history->get_result();
    while ($row = $result_history->fetch_assoc()) {
        $borrowing_history[] = $row;
    }
    $stmt_history->close();
}

// Fetch notifications (simplified mock for now)
$notifications = [
    ['id' => '1', 'type' => 'due', 'title' => 'Book Due Soon', 'message' => 'Your book "Introduction to Computer Science" is due on ' . date('Y-m-d', strtotime('+1 day')) . '.', 'time' => '2 hours ago'],
    ['id' => '2', 'type' => 'overdue', 'title' => 'Overdue Book Alert', 'message' => 'Your book "Advanced Mathematics" is overdue. Penalty applies.', 'time' => '1 day ago'],
    ['id' => '3', 'type' => 'new', 'title' => 'New Arrivals', 'message' => 'Check out new books in Computer Science category!', 'time' => '3 days ago'],
];

// Fetch featured books (mock data for now)
$featured_books = [
    ['id' => '6', 'title' => 'Machine Learning Fundamentals', 'author' => 'Dr. Alex Kumar', 'category' => 'Computer Science', 'available' => true, 'rating' => 4.8],
    ['id' => '7', 'title' => 'Digital Signal Processing', 'author' => 'Maria Rodriguez', 'category' => 'Engineering', 'available' => true, 'rating' => 4.6],
    ['id' => '8', 'title' => 'Modern Physics', 'author' => 'Robert Johnson', 'category' => 'Physics', 'available' => false, 'rating' => 4.9],
];

function getDaysUntilDue($dueDate) {
    $due = new DateTime($dueDate);
    $today = new DateTime();
    $interval = $today->diff($due);
    return (int)$interval->format('%R%a'); // Returns +days or -days
}

?>

<div class="min-h-screen bg-background">
    <!-- Header (already included by header.php) -->

    <!-- Main Content -->
    <main class="p-6 space-y-6">
        <!-- Welcome Section -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[var(--dark-charcoal)] mb-2">Welcome back, <?php echo htmlspecialchars(explode(' ', $full_name)[0]); ?>! 🌊</h1>
                <p class="text-[var(--dark-charcoal)]/70 text-lg">
                    Explore your digital library with AI-powered assistance and discover new knowledge
                </p>
            </div>
            <a href="books_available.php" class="btn btn-info shadow-lg px-6 py-3 rounded-xl">
                <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                Browse Books
            </a>
        </div>

        <!-- Quick Stats -->
        <div class="grid gap-6 md:grid-cols-5">
            <div class="card border-0 shadow-lg bg-gradient-to-br from-[var(--card-orange-light)]/10 to-[var(--card-orange-light)]/5 hover:shadow-xl transition-all duration-300">
                <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                    <div class="card-title text-sm font-medium text-[var(--dark-charcoal)]">Current Loans</div>
                    <div class="w-10 h-10 bg-[var(--card-orange-light)] rounded-xl flex items-center justify-center">
                        <i data-lucide="book-marked" class="h-5 w-5 text-white"></i>
                    </div>
                </div>
                <div class="card-content">
                    <div class="text-3xl font-bold text-[var(--dark-charcoal)] mb-1"><?php echo count($current_loans); ?></div>
                    <p class="text-sm text-[var(--dark-charcoal)]/70">
                        <?php echo count(array_filter($current_loans, function($loan) { return $loan['status'] === 'overdue'; })); ?> overdue
                    </p>
                </div>
            </div>
            
            <div class="card border-0 shadow-lg bg-gradient-to-br from-[var(--card-yellow-light)]/10 to-[var(--card-yellow-light)]/5 hover:shadow-xl transition-all duration-300">
                <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                    <div class="card-title text-sm font-medium text-[var(--dark-charcoal)]">Books Read</div>
                    <div class="w-10 h-10 bg-[var(--card-yellow-light)] rounded-xl flex items-center justify-center">
                        <i data-lucide="book-open" class="h-5 w-5 text-[var(--dark-charcoal)]"></i>
                    </div>
                </div>
                <div class="card-content">
                    <div class="text-3xl font-bold text-[var(--dark-charcoal)] mb-1"><?php echo count($borrowing_history); ?></div>
                    <p class="text-sm text-[var(--dark-charcoal)]/70">
                        This semester
                    </p>
                </div>
            </div>
            
            <div class="card border-0 shadow-lg bg-gradient-to-br from-[var(--card-blue-light)]/10 to-[var(--card-blue-light)]/5 hover:shadow-xl transition-all duration-300">
                <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                    <div class="card-title text-sm font-medium text-[var(--dark-charcoal)]">Due Soon</div>
                    <div class="w-10 h-10 bg-[var(--card-blue-light)] rounded-xl flex items-center justify-center">
                        <i data-lucide="clock" class="h-5 w-5 text-white"></i>
                    </div>
                </div>
                <div class="card-content">
                    <div class="text-3xl font-bold text-[var(--dark-charcoal)] mb-1">
                        <?php echo count(array_filter($current_loans, function($loan) { return getDaysUntilDue($loan['due_date']) <= 3 && $loan['status'] !== 'overdue'; })); ?>
                    </div>
                    <p class="text-sm text-[var(--dark-charcoal)]/70">
                        Within 3 days
                    </p>
                </div>
            </div>
            
            <div class="card border-0 shadow-lg bg-gradient-to-br from-[var(--card-pink-light)]/10 to-[var(--card-pink-light)]/5 hover:shadow-xl transition-all duration-300">
                <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                    <div class="card-title text-sm font-medium text-[var(--dark-charcoal)]">Overdue</div>
                    <div class="w-10 h-10 bg-[var(--card-pink-light)] rounded-xl flex items-center justify-center">
                        <i data-lucide="alert-triangle" class="h-5 w-5 text-white"></i>
                    </div>
                </div>
                <div class="card-content">
                    <div class="text-3xl font-bold text-[var(--card-pink-light)] mb-1">
                        <?php echo count(array_filter($current_loans, function($loan) { return $loan['status'] === 'overdue'; })); ?>
                    </div>
                    <p class="text-sm text-[var(--dark-charcoal)]/70">
                        Needs attention
                    </p>
                </div>
            </div>
            
            <div class="card border-0 shadow-lg bg-gradient-to-br from-red-500/10 to-red-600/5 hover:shadow-xl transition-all duration-300">
                <div class="card-header flex flex-row items-center justify-between space-y-0 pb-2">
                    <div class="card-title text-sm font-medium text-[var(--dark-charcoal)]">Penalties</div>
                    <div class="w-10 h-10 bg-red-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-lg">₱</span>
                    </div>
                </div>
                <div class="card-content">
                    <div class="text-3xl font-bold text-red-600 mb-1">
                        ₱<?php echo number_format($total_penalties, 2); ?>
                    </div>
                    <p class="text-sm text-[var(--dark-charcoal)]/70">
                        Outstanding fees
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-8 md:grid-cols-2">
            <!-- Current Loans -->
            <div class="card border-0 shadow-lg bg-white/95 backdrop-blur-sm">
                <div class="card-header bg-gradient-to-r from-[var(--card-orange-light)]/10 to-[var(--card-yellow-light)]/10 rounded-t-lg">
                    <div class="card-title text-xl text-[var(--dark-charcoal)] flex items-center">
                        <i data-lucide="book-marked" class="w-5 h-5 mr-2 text-[var(--card-orange-light)]"></i>
                        Current Loans
                    </div>
                    <div class="card-description text-[var(--dark-charcoal)]/70">Books you currently have borrowed</div>
                </div>
                <div class="card-content">
                    <div class="space-y-4">
                        <?php if (!empty($current_loans)): ?>
                            <?php foreach ($current_loans as $book): ?>
                                <?php
                                $daysUntilDue = getDaysUntilDue($book['due_date']);
                                $penalty = $book['penalty_amount'];
                                ?>
                                <div class="flex items-center space-x-3 p-4 border-0 rounded-xl bg-gradient-to-r from-[var(--card-yellow-light)]/5 to-[var(--card-blue-light)]/5 hover:shadow-md transition-all duration-300">
                                    <div class="w-12 h-16 bg-gradient-to-br from-[var(--card-orange-light)] to-[var(--card-yellow-light)] rounded-lg flex items-center justify-center shadow-lg">
                                        <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-[var(--dark-charcoal)]"><?php echo htmlspecialchars($book['title']); ?></h4>
                                        <p class="text-sm text-[var(--dark-charcoal)]/70"><?php echo htmlspecialchars($book['author_name'] ?? 'N/A'); ?></p>
                                        <div class="flex items-center space-x-2 mt-2 flex-wrap">
                                            <i data-lucide="calendar" class="w-3 h-3 text-[var(--card-blue-light)]"></i>
                                            <span class="text-xs text-[var(--dark-charcoal)]/70">Due: <?php echo htmlspecialchars($book['due_date']); ?></span>
                                            <?php if ($book['status'] === 'overdue'): ?>
                                                <span class="badge bg-[var(--card-pink-light)] text-white">Overdue</span>
                                                <span class="badge bg-red-600 text-white">Fine: ₱<?php echo number_format($penalty, 2); ?></span>
                                            <?php elseif ($daysUntilDue <= 3 && $daysUntilDue >= 0): ?>
                                                <span class="badge bg-[var(--card-yellow-light)] text-[var(--dark-charcoal)]">Due Soon</span>
                                            <?php else: ?>
                                                <span class="badge bg-[var(--card-green-light)] text-white">On Time</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-muted-foreground py-4">
                                No current loans. Browse books to get started!
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Featured Books -->
            <div class="card border-0 shadow-lg bg-white/95 backdrop-blur-sm">
                <div class="card-header bg-gradient-to-r from-[var(--card-blue-light)]/10 to-[var(--card-green-light)]/10 rounded-t-lg">
                    <div class="card-title text-xl text-[var(--dark-charcoal)] flex items-center">
                        <i data-lucide="star" class="w-5 h-5 mr-2 text-[var(--card-yellow-light)]"></i>
                        Featured Books
                    </div>
                    <div class="card-description text-[var(--dark-charcoal)]/70">Popular and newly added coastal treasures</div>
                </div>
                <div class="card-content">
                    <div class="space-y-4">
                        <?php foreach ($featured_books as $book): ?>
                            <?php
                            $categoryColors = [
                                'Computer Science' => 'var(--card-orange-light)',
                                'Engineering' => 'var(--card-blue-light)',
                                'Physics' => 'var(--card-green-light)'
                            ];
                            $categoryColor = $categoryColors[$book['category']] ?? 'var(--primary)';
                            ?>
                            <div class="flex items-center space-x-3 p-4 border-0 rounded-xl bg-gradient-to-r from-[var(--card-green-light)]/5 to-[var(--card-pink-light)]/5 cursor-pointer hover:shadow-md transition-all duration-300 hover:scale-[1.02]"
                                onclick="window.location.href='book_details.php?book_id=<?php echo $book['id']; ?>'">
                                <div class="w-12 h-16 bg-gradient-to-br from-[var(--card-blue-light)] to-[var(--card-green-light)] rounded-lg flex items-center justify-center shadow-lg">
                                    <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-[var(--dark-charcoal)]"><?php echo htmlspecialchars($book['title']); ?></h4>
                                    <p class="text-sm text-[var(--dark-charcoal)]/70"><?php echo htmlspecialchars($book['author']); ?></p>
                                    <div class="flex items-center space-x-2 mt-2">
                                        <i data-lucide="star" class="w-3 h-3 fill-[var(--card-yellow-light)] text-[var(--card-yellow-light)]"></i>
                                        <span class="text-xs text-[var(--dark-charcoal)]/70"><?php echo htmlspecialchars($book['rating']); ?></span>
                                        <span class="text-xs text-[var(--dark-charcoal)]/50">•</span>
                                        <span class="badge" style="background-color: <?php echo $categoryColor; ?>20; color: <?php echo $categoryColor; ?>; border: 1px solid <?php echo $categoryColor; ?>40;">
                                            <?php echo htmlspecialchars($book['category']); ?>
                                        </span>
                                        <?php if ($book['available']): ?>
                                            <span class="badge bg-[var(--card-green-light)] text-white">Available</span>
                                        <?php else: ?>
                                            <span class="badge bg-[var(--card-pink-light)]/20 text-[var(--card-pink-light)]">Checked Out</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-lg bg-gradient-to-r from-[var(--card-orange-light)]/5 via-white to-[var(--card-blue-light)]/5">
            <div class="card-header">
                <div class="card-title text-xl text-[var(--dark-charcoal)]">⚡ Quick Actions</div>
                <div class="card-description text-[var(--dark-charcoal)]/70">Common tasks and AI-powered shortcuts</div>
            </div>
            <div class="card-content">
                <div class="grid gap-4 md:grid-cols-4">
                    <a href="books_available.php" class="btn btn-outline justify-start border-[var(--card-orange-light)] text-[var(--card-orange-light)] hover:bg-[var(--card-orange-light)] hover:text-white py-4 rounded-xl shadow-md">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Search Books
                    </a>
                    <button onclick="toggleAIChat()" class="btn btn-outline justify-start border-[var(--card-yellow-light)] text-[var(--card-yellow-light)] hover:bg-[var(--card-yellow-light)] hover:text-[var(--dark-charcoal)] py-4 rounded-xl shadow-md">
                        <i data-lucide="message-circle" class="w-4 h-4 mr-2"></i>
                        AI Assistant
                    </button>
                    <a href="my_loans.php" class="btn btn-outline justify-start border-[var(--card-green-light)] text-[var(--card-green-light)] hover:bg-[var(--card-green-light)] hover:text-white py-4 rounded-xl shadow-md">
                        <i data-lucide="book-marked" class="w-4 h-4 mr-2"></i>
                        My Loans
                    </a>
                    <a href="my_loans.php" class="btn btn-outline justify-start border-[var(--card-blue-light)] text-[var(--card-blue-light)] hover:bg-[var(--card-blue-light)] hover:text-white py-4 rounded-xl shadow-md">
                        <i data-lucide="clock" class="w-4 h-4 mr-2"></i>
                        Loan History
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Profile Modal (Placeholder) -->
    <div id="profileModal" class="ai-chat-modal" style="display: none;">
        <div class="ai-chat-content">
            <div class="ai-chat-header">
                <i data-lucide="user" class="ai-chat-icon"></i>
                <span class="ai-chat-title">Profile Settings</span>
                <button class="ai-chat-close-btn" onclick="document.getElementById('profileModal').style.display='none';">&times;</button>
            </div>
            <div class="ai-chat-body">
                <p class="text-muted-foreground">Profile settings would be displayed here.</p>
                <button class="btn btn-primary" onclick="document.getElementById('profileModal').style.display='none';">Close</button>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>