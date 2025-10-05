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
        JOIN users u ON l.student_id = u.user_id
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

<div class="table-container">
    <h2>Manage Borrowed Books</h2>
    <table>
        <thead>
            <tr>
                <th>Book Title</th>
                <th>Borrower</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($loans)): ?>
                <?php foreach ($loans as $loan): ?>
                    <tr class="<?php echo ($loan['status'] == 'overdue') ? 'overdue-row' : ''; ?>">
                        <td><?php echo htmlspecialchars($loan['title']); ?></td>
                        <td><?php echo htmlspecialchars($loan['borrower_name']); ?></td>
                        <td><?php echo htmlspecialchars($loan['borrow_date']); ?></td>
                        <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($loan['status'])); ?></td>
                        <td>
                            <?php if ($loan['status'] !== 'returned'): ?>
                                <button class="btn btn-success return-btn" onclick="returnBook(<?php echo $loan['loan_id']; ?>)">Return</button>
                                <button class="btn btn-warning extend-btn" onclick="extendDueDate(<?php echo $loan['loan_id']; ?>, '<?php echo $loan['due_date']; ?>')">Extend</button>
                                <button class="btn btn-danger penalty-btn" onclick="assessPenalty(<?php echo $loan['loan_id']; ?>)">Penalty</button>
                            <?php else: ?>
                                Returned
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan='6'>No books currently borrowed or overdue.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
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
                location.reload(); // Reload to update the table
            }
        }
    }

    async function extendDueDate(loanId, currentDueDate) {
        const newDueDate = prompt(`Enter new due date (YYYY-MM-DD) for loan ID ${loanId}. Current: ${currentDueDate}`);
        if (newDueDate) {
            const response = await fetch('process_loan.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON_stringify({ action: 'extend', loan_id: loanId, new_due_date: newDueDate })
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
                body: JSON_stringify({ action: 'assess_penalty', loan_id: loanId, amount: parseFloat(penaltyAmount) })
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

<?php
require_once 'footer.php';
?>