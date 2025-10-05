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
?>

<div class="table-container">
    <h2>My Current Loans & History</h2>
    <table>
        <thead>
            <tr>
                <th>Book Title</th>
                <th>Author</th>
                <th>Borrow Date</th>
                <th>Due Date</th>
                <th>Return Date</th>
                <th>Status</th>
                <th>Penalty (if any)</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($loans)): ?>
                <?php foreach ($loans as $loan): ?>
                    <tr class="<?php echo ($loan['status'] == 'overdue' && !$loan['return_date']) ? 'overdue-row' : ''; ?>">
                        <td><?php echo htmlspecialchars($loan['title']); ?></td>
                        <td><?php echo htmlspecialchars($loan['author_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($loan['borrow_date']); ?></td>
                        <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                        <td><?php echo htmlspecialchars($loan['return_date'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($loan['status'])); ?></td>
                        <td>
                            <?php
                            if ($loan['pending_penalty'] > 0) {
                                echo '₱' . number_format($loan['pending_penalty'], 2) . ' (Pending)';
                            } elseif ($loan['status'] == 'overdue' && !$loan['return_date']) {
                                // Simplified penalty calculation for display if not yet assessed
                                $today = new DateTime();
                                $dueDate = new DateTime($loan['due_date']);
                                if ($today > $dueDate) {
                                    $interval = $today->diff($dueDate);
                                    $daysOverdue = $interval->days;
                                    $dailyPenalty = 10.00; // Example daily penalty
                                    echo '₱' . number_format($daysOverdue * $dailyPenalty, 2) . ' (Estimated Overdue)';
                                } else {
                                    echo '-';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan='7'>You have no loan records.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'footer.php';
?>