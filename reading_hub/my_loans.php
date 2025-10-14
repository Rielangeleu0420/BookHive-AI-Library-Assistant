<?php
require_once 'header.php';

// Redirect if not a student
if (getUserRole() !== 'student') {
    header("Location: librarian_dashboard.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$loans = [];

$sql = "SELECT l.loan_id, b.title, a.author_name, l.borrow_date, l.due_date, l.return_date, l.status,
               b.book_id, b.quantity_available,
               (SELECT SUM(p.amount) FROM penalties p WHERE p.loan_id = l.loan_id AND p.status = 'pending') AS pending_penalty
        FROM loans l
        JOIN books b ON l.book_id = b.book_id
        LEFT JOIN authors a ON b.author_id = a.author_id
        WHERE l.student_id = ?
        ORDER BY l.due_date ASC";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
    $stmt->close();
} else {
    echo "Error: " . $conn->error;
}

// Handle return book action
if (isset($_POST['return_book'])) {
    $loan_id = $_POST['loan_id'];
    
    // Update loan status to returned
    $return_sql = "UPDATE loans SET return_date = CURDATE(), status = 'returned' WHERE loan_id = ?";
    if ($stmt = $conn->prepare($return_sql)) {
        $stmt->bind_param("i", $loan_id);
        if ($stmt->execute()) {
            // Get book_id to update quantity
            $book_sql = "SELECT book_id FROM loans WHERE loan_id = ?";
            if ($book_stmt = $conn->prepare($book_sql)) {
                $book_stmt->bind_param("i", $loan_id);
                $book_stmt->execute();
                $book_stmt->bind_result($book_id);
                $book_stmt->fetch();
                $book_stmt->close();
                
                // Update book quantity
                $update_qty_sql = "UPDATE books SET quantity_available = quantity_available + 1 WHERE book_id = ?";
                if ($update_stmt = $conn->prepare($update_qty_sql)) {
                    $update_stmt->bind_param("i", $book_id);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
            }
            
            // Log the action
            logAudit($student_id, 'return_book', $loan_id, 'Returned book for loan ID: ' . $loan_id);
            
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Book Returned!',
                    text: 'The book has been successfully returned.',
                    confirmButtonColor: '#28A745'
                }).then(() => {
                    window.location.href = 'my_loans.php';
                });
            </script>";
        }
        $stmt->close();
    }
}

// Handle renew loan action
if (isset($_POST['renew_loan'])) {
    $loan_id = $_POST['loan_id'];
    
    // Calculate new due date (extend by 14 days from current due date)
    $renew_sql = "UPDATE loans SET due_date = DATE_ADD(due_date, INTERVAL 14 DAY) WHERE loan_id = ? AND status = 'borrowed'";
    if ($stmt = $conn->prepare($renew_sql)) {
        $stmt->bind_param("i", $loan_id);
        if ($stmt->execute()) {
            // Log the action
            logAudit($student_id, 'renew_loan', $loan_id, 'Renewed loan ID: ' . $loan_id);
            
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Loan Renewed!',
                    text: 'Your loan has been extended for 14 more days.',
                    confirmButtonColor: '#17A2B8'
                }).then(() => {
                    window.location.href = 'my_loans.php';
                });
            </script>";
        }
        $stmt->close();
    }
}

// Calculate statistics
$stats = [
    'borrowed' => 0,
    'overdue' => 0,
    'returned' => 0,
    'penalty' => 0
];

foreach ($loans as $loan) {
    if ($loan['status'] == 'borrowed') {
        $stats['borrowed']++;
        // Check if due within 3 days or overdue
        $due_date = new DateTime($loan['due_date']);
        $today = new DateTime();
        if ($due_date < $today) {
            $stats['overdue']++;
        }
    } elseif ($loan['status'] == 'returned') {
        $stats['returned']++;
    }
    
    if ($loan['pending_penalty'] > 0) {
        $stats['penalty']++;
    }
}
?>

<link rel="stylesheet" href="loans.css">

<div class="loans-container">
    <div class="loans-content">
        <div class="loans-header">
            <i data-lucide="book-open-text" class="header-icon"></i>
            <h2>My Loans & History</h2>
            <p>Track your borrowed books and reading history</p>
        </div>

        <div class="loans-grid">
            <!-- Statistics Cards -->
            <div class="loans-stats">
                <div class="stat-card borrowed">
                    <i data-lucide="book-open" class="stat-icon"></i>
                    <div class="stat-number"><?php echo $stats['borrowed']; ?></div>
                    <div class="stat-label">Currently Borrowed</div>
                </div>
                <div class="stat-card overdue">
                    <i data-lucide="alert-triangle" class="stat-icon"></i>
                    <div class="stat-number"><?php echo $stats['overdue']; ?></div>
                    <div class="stat-label">Overdue Books</div>
                </div>
                <div class="stat-card returned">
                    <i data-lucide="check-circle" class="stat-icon"></i>
                    <div class="stat-number"><?php echo $stats['returned']; ?></div>
                    <div class="stat-label">Returned Books</div>
                </div>
                <div class="stat-card penalty">
                    <i data-lucide="dollar-sign" class="stat-icon"></i>
                    <div class="stat-number"><?php echo $stats['penalty']; ?></div>
                    <div class="stat-label">Pending Penalties</div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="loans-filter" id="loansFilter">
                <div class="filter-tab active" data-filter="all">All Loans</div>
                <div class="filter-tab" data-filter="borrowed">Currently Borrowed</div>
                <div class="filter-tab" data-filter="overdue">Overdue</div>
                <div class="filter-tab" data-filter="returned">Returned</div>
                <div class="filter-tab" data-filter="penalty">With Penalties</div>
            </div>

            <!-- Loans Cards -->
            <div class="loan-cards" id="loanCards">
                <?php if (!empty($loans)): ?>
                    <?php foreach ($loans as $loan): ?>
                        <?php
                        // Determine card status and styling
                        $card_class = '';
                        $status_class = '';
                        $due_date_class = '';
                        $can_renew = false;
                        
                        if ($loan['return_date']) {
                            $card_class = 'returned';
                            $status_class = 'returned';
                            $due_date_class = 'returned';
                        } else {
                            $today = new DateTime();
                            $due_date = new DateTime($loan['due_date']);
                            
                            if ($today > $due_date) {
                                $card_class = 'overdue';
                                $status_class = 'overdue';
                                $due_date_class = 'overdue';
                            } else {
                                $card_class = 'borrowed';
                                $status_class = 'borrowed';
                                $interval = $today->diff($due_date);
                                if ($interval->days <= 3) {
                                    $due_date_class = 'due-soon';
                                }
                                
                                // Check if can renew (not overdue and not already renewed multiple times)
                                $can_renew = true;
                            }
                        }
                        
                        // Calculate estimated penalty for display
                        $estimated_penalty = 0;
                        if ($loan['pending_penalty'] > 0) {
                            $estimated_penalty = $loan['pending_penalty'];
                        } elseif ($card_class == 'overdue' && !$loan['return_date']) {
                            $today = new DateTime();
                            $due_date = new DateTime($loan['due_date']);
                            $interval = $today->diff($due_date);
                            $daysOverdue = $interval->days;
                            $dailyPenalty = 10.00;
                            $estimated_penalty = $daysOverdue * $dailyPenalty;
                        }
                        ?>
                        
                        <div class="loan-card <?php echo $card_class; ?>" data-status="<?php echo $card_class; ?>" data-penalty="<?php echo ($estimated_penalty > 0) ? 'yes' : 'no'; ?>">
                            <div class="loan-card-header">
                                <div class="loan-book-info">
                                    <h3 class="loan-book-title"><?php echo htmlspecialchars($loan['title']); ?></h3>
                                    <p class="loan-book-author">
                                        <i data-lucide="user" class="meta-icon"></i>
                                        <?php echo htmlspecialchars($loan['author_name'] ?? 'Unknown Author'); ?>
                                    </p>
                                </div>
                                <div class="loan-status <?php echo $status_class; ?>">
                                    <?php echo ucfirst($loan['status']); ?>
                                </div>
                            </div>

                            <div class="loan-details">
                                <div class="loan-detail">
                                    <span class="detail-label">Borrow Date</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($loan['borrow_date']); ?></span>
                                </div>
                                <div class="loan-detail">
                                    <span class="detail-label">Due Date</span>
                                    <span class="detail-value <?php echo $due_date_class; ?>">
                                        <?php echo htmlspecialchars($loan['due_date']); ?>
                                        <?php if ($due_date_class == 'due-soon'): ?>
                                            <i data-lucide="clock-alert" class="meta-icon" style="margin-left: 5px;"></i>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="loan-detail">
                                    <span class="detail-label">Return Date</span>
                                    <span class="detail-value">
                                        <?php echo htmlspecialchars($loan['return_date'] ?? 'Not returned yet'); ?>
                                    </span>
                                </div>
                                <div class="loan-detail">
                                    <span class="detail-label">Loan ID</span>
                                    <span class="detail-value">#<?php echo htmlspecialchars($loan['loan_id']); ?></span>
                                </div>
                            </div>

                            <?php if ($estimated_penalty > 0): ?>
                                <div class="loan-penalty">
                                    <div class="penalty-header">
                                        <i data-lucide="alert-circle" class="penalty-icon"></i>
                                        <span class="penalty-title">Pending Penalty</span>
                                    </div>
                                    <div class="penalty-amount">₱<?php echo number_format($estimated_penalty, 2); ?></div>
                                    <div class="penalty-note">
                                        <?php if ($loan['pending_penalty'] > 0): ?>
                                            This penalty needs to be paid. Please visit the library.
                                        <?php else: ?>
                                            Estimated overdue penalty. Final amount will be calculated upon return.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="loan-actions">
                                <?php if (!$loan['return_date']): ?>
                                    <?php if ($can_renew): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                            <button type="submit" name="renew_loan" class="btn-action btn-renew">
                                                <i data-lucide="refresh-cw" class="btn-icon"></i>
                                                Renew Loan
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                                        <button type="submit" name="return_book" class="btn-action btn-return" onclick="return confirmReturn(this)">
                                            <i data-lucide="check" class="btn-icon"></i>
                                            Return Book
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <?php if ($estimated_penalty > 0): ?>
                                    <button class="btn-action btn-pay" onclick="payPenalty(<?php echo $loan['loan_id']; ?>)">
                                        <i data-lucide="dollar-sign" class="btn-icon"></i>
                                        Pay Penalty
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i data-lucide="book-x" class="empty-icon"></i>
                        <h3>No Loan Records Found</h3>
                        <p>You haven't borrowed any books yet. Start exploring our library collection!</p>
                        <a href="books.php" class="btn-browse">
                            <i data-lucide="search" class="btn-icon"></i>
                            Browse Books
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Filter functionality
        const filterTabs = document.querySelectorAll('.filter-tab');
        const loanCards = document.querySelectorAll('.loan-card');
        
        filterTabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                filterTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.getAttribute('data-filter');
                
                // Filter cards
                loanCards.forEach(card => {
                    const status = card.getAttribute('data-status');
                    const hasPenalty = card.getAttribute('data-penalty');
                    
                    switch (filter) {
                        case 'all':
                            card.style.display = 'block';
                            break;
                        case 'borrowed':
                            card.style.display = status === 'borrowed' ? 'block' : 'none';
                            break;
                        case 'overdue':
                            card.style.display = status === 'overdue' ? 'block' : 'none';
                            break;
                        case 'returned':
                            card.style.display = status === 'returned' ? 'block' : 'none';
                            break;
                        case 'penalty':
                            card.style.display = hasPenalty === 'yes' ? 'block' : 'none';
                            break;
                    }
                });
            });
        });

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    function confirmReturn(button) {
        if (!confirm('Are you sure you want to mark this book as returned? This action cannot be undone.')) {
            return false;
        }
        return true;
    }

    function payPenalty(loanId) {
        Swal.fire({
            title: 'Pay Penalty',
            html: 'You will be redirected to the payment page to settle your penalty.<br><br>Please have your payment ready.',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#FFC107',
            cancelButtonColor: '#8B7355',
            confirmButtonText: 'Proceed to Payment',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect to payment page - create this file if it doesn't exist
                window.location.href = 'pay_penalty.php?loan_id=' + loanId;
            }
        });
    }

    // Add keyboard navigation for filters
    document.addEventListener('keydown', function(e) {
        const filterTabs = document.querySelectorAll('.filter-tab');
        const activeTab = document.querySelector('.filter-tab.active');
        let currentIndex = Array.from(filterTabs).indexOf(activeTab);
        
        if (e.key === 'ArrowRight') {
            currentIndex = (currentIndex + 1) % filterTabs.length;
            filterTabs[currentIndex].click();
        } else if (e.key === 'ArrowLeft') {
            currentIndex = (currentIndex - 1 + filterTabs.length) % filterTabs.length;
            filterTabs[currentIndex].click();
        }
    });
</script>

<?php
require_once 'footer.php';
?>
