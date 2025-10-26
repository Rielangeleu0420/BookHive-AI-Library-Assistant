<?php
require_once 'header.php';

// Redirect if not a librarian
if (getUserRole() !== 'librarian') {
    header("Location: student_dashboard.php");
    exit();
}

$loans = [];
$sql = "SELECT l.loan_id, b.title, u.full_name AS borrower_name, l.borrow_date, l.due_date, l.return_date, l.status
        FROM loans l
        JOIN books b ON l.book_id = b.book_id
        JOIN users u ON l.user_id = u.user_id
        WHERE l.status = 'borrowed' OR l.status = 'overdue'
        ORDER BY l.due_date ASC";

if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
    $result->free();
} else {
    echo "Error: " . $conn->error;
}
?>

<link rel="stylesheet" href="borrowed_books.css">

<div class="borrowed-books-container">
    <div class="borrowed-books-content">
        <div class="borrowed-books-header">
            <i data-lucide="book-open-check" class="header-icon"></i>
            <h2>Manage Borrowed Books</h2>
            <p>Track and manage all currently borrowed and overdue books</p>
        </div>

        <div class="stats-cards">
            <div class="stat-card">
                <i data-lucide="book-open" class="stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-value"><?php echo count($loans); ?></div>
                    <div class="stat-label">Total Active Loans</div>
                </div>
            </div>
            <div class="stat-card">
                <i data-lucide="alert-triangle" class="stat-icon overdue"></i>
                <div class="stat-content">
                    <div class="stat-value overdue">
                        <?php echo count(array_filter($loans, function($loan) { return $loan['status'] == 'overdue'; })); ?>
                    </div>
                    <div class="stat-label">Overdue Books</div>
                </div>
            </div>
            <div class="stat-card">
                <i data-lucide="calendar" class="stat-icon"></i>
                <div class="stat-content">
                    <div class="stat-value">
                        <?php echo count(array_filter($loans, function($loan) { 
                            return $loan['status'] == 'borrowed' && strtotime($loan['due_date']) - time() < 3 * 24 * 60 * 60; 
                        })); ?>
                    </div>
                    <div class="stat-label">Due Soon (3 days)</div>
                </div>
            </div>
        </div>

        <div class="table-section">
            <div class="table-header">
                <h3>Current Loans</h3>
                <div class="table-actions">
                    <button class="btn-filter active" data-filter="all">All</button>
                    <button class="btn-filter" data-filter="overdue">Overdue</button>
                    <button class="btn-filter" data-filter="borrowed">Borrowed</button>
                </div>
            </div>

            <div class="table-container">
                <table class="loans-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Borrower</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($loans)): ?>
                            <?php foreach ($loans as $loan): ?>
                                <tr class="loan-row <?php echo ($loan['status'] == 'overdue') ? 'overdue' : 'active'; ?>" data-status="<?php echo $loan['status']; ?>">
                                    <td>
                                        <div class="book-info">
                                            <i data-lucide="book-open" class="book-icon"></i>
                                            <span class="book-title"><?php echo htmlspecialchars($loan['title']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="borrower-info">
                                            <i data-lucide="user" class="user-icon"></i>
                                            <span><?php echo htmlspecialchars($loan['borrower_name']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info">
                                            <i data-lucide="calendar" class="date-icon"></i>
                                            <span><?php echo htmlspecialchars($loan['borrow_date']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-info <?php echo ($loan['status'] == 'overdue') ? 'overdue-date' : ''; ?>">
                                            <i data-lucide="clock" class="date-icon"></i>
                                            <span><?php echo htmlspecialchars($loan['due_date']); ?></span>
                                            <?php if ($loan['status'] == 'overdue'): ?>
                                                <span class="overdue-badge">Overdue</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $loan['status']; ?>">
                                            <i data-lucide="<?php echo $loan['status'] == 'overdue' ? 'alert-triangle' : 'book-open'; ?>"></i>
                                            <?php echo htmlspecialchars(ucfirst($loan['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn-action btn-return" onclick="returnBook(<?php echo $loan['loan_id']; ?>, '<?php echo htmlspecialchars($loan['title']); ?>')" title="Mark as Returned">
                                                <i data-lucide="check-circle"></i>
                                            </button>
                                            <button class="btn-action btn-extend" onclick="extendDueDate(<?php echo $loan['loan_id']; ?>, '<?php echo $loan['due_date']; ?>', '<?php echo htmlspecialchars($loan['title']); ?>')" title="Extend Due Date">
                                                <i data-lucide="calendar-plus"></i>
                                            </button>
                                            <button class="btn-action btn-penalty" onclick="assessPenalty(<?php echo $loan['loan_id']; ?>, '<?php echo htmlspecialchars($loan['title']); ?>')" title="Assess Penalty">
                                                <i data-lucide="dollar-sign"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr class="no-loans">
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i data-lucide="book-open" class="empty-icon"></i>
                                        <h4>No Active Loans</h4>
                                        <p>There are no currently borrowed or overdue books.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const filterButtons = document.querySelectorAll('.btn-filter');
        const loanRows = document.querySelectorAll('.loan-row');

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');
                
                // Update active button
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Filter rows
                loanRows.forEach(row => {
                    if (filter === 'all' || row.getAttribute('data-status') === filter) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        // Initialize Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    });

    async function returnBook(loanId, bookTitle) {
        const result = await Swal.fire({
            title: 'Return Book',
            html: `<div style="text-align: left;">
                <p>Are you sure you want to mark this book as returned?</p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <strong>Book:</strong> ${bookTitle}<br>
                    <strong>Loan ID:</strong> ${loanId}
                </div>
            </div>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Mark as Returned',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('process_loan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'return', loan_id: loanId })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Book Returned!',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    });
                    location.reload();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonColor: '#dc3545'
                });
            }
        }
    }

    async function extendDueDate(loanId, currentDueDate, bookTitle) {
        const { value: newDueDate } = await Swal.fire({
            title: 'Extend Due Date',
            html: `<div style="text-align: left;">
                <p>Enter new due date for:</p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <strong>Book:</strong> ${bookTitle}<br>
                    <strong>Current Due Date:</strong> ${currentDueDate}
                </div>
            </div>`,
            input: 'date',
            inputLabel: 'New Due Date',
            inputValue: currentDueDate,
            showCancelButton: true,
            confirmButtonText: 'Extend Due Date',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please select a due date';
                }
                if (new Date(value) <= new Date(currentDueDate)) {
                    return 'New due date must be after current due date';
                }
            }
        });

        if (newDueDate) {
            try {
                const response = await fetch('process_loan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'extend', loan_id: loanId, new_due_date: newDueDate })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Due Date Extended!',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    });
                    location.reload();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonColor: '#dc3545'
                });
            }
        }
    }

    async function assessPenalty(loanId, bookTitle) {
        const { value: penaltyAmount } = await Swal.fire({
            title: 'Assess Penalty',
            html: `<div style="text-align: left;">
                <p>Enter penalty amount for:</p>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <strong>Book:</strong> ${bookTitle}<br>
                    <strong>Loan ID:</strong> ${loanId}
                </div>
            </div>`,
            input: 'number',
            inputLabel: 'Penalty Amount (₱)',
            inputPlaceholder: 'Enter amount...',
            showCancelButton: true,
            confirmButtonText: 'Assess Penalty',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6b7280',
            inputValidator: (value) => {
                if (!value || parseFloat(value) <= 0) {
                    return 'Please enter a valid positive amount';
                }
            }
        });

        if (penaltyAmount) {
            try {
                const response = await fetch('process_loan.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'assess_penalty', loan_id: loanId, amount: parseFloat(penaltyAmount) })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    await Swal.fire({
                        icon: 'success',
                        title: 'Penalty Assessed!',
                        text: data.message,
                        confirmButtonColor: '#10b981'
                    });
                    location.reload();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonColor: '#dc3545'
                });
            }
        }
    }
</script>

<?php
require_once 'footer.php';
?>