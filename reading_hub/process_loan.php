<?php
require_once 'functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserRole() !== 'librarian') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? '';
$loan_id = $data['loan_id'] ?? 0;
$librarian_id = $_SESSION['user_id'];

if ($loan_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid loan ID.']);
    exit();
}

switch ($action) {
    case 'return':
        // Get book_id and student_id from loan
        $sql_get_loan_info = "SELECT book_id, student_id FROM loans WHERE loan_id = ?";
        $stmt_get_loan_info = $conn->prepare($sql_get_loan_info);
        $stmt_get_loan_info->bind_param("i", $loan_id);
        $stmt_get_loan_info->execute();
        $stmt_get_loan_info->bind_result($book_id, $student_id);
        $stmt_get_loan_info->fetch();
        $stmt_get_loan_info->close();

        if (!$book_id) {
            echo json_encode(['success' => false, 'message' => 'Loan not found.']);
            exit();
        }

        // Update loan status and return date
        $sql_update_loan = "UPDATE loans SET status = 'returned', return_date = CURDATE() WHERE loan_id = ?";
        $stmt_update_loan = $conn->prepare($sql_update_loan);
        $stmt_update_loan->bind_param("i", $loan_id);

        // Increment book quantity_available
        $sql_update_book_qty = "UPDATE books SET quantity_available = quantity_available + 1 WHERE book_id = ?";
        $stmt_update_book_qty = $conn->prepare($sql_update_book_qty);
        $stmt_update_book_qty->bind_param("i", $book_id);

        if ($stmt_update_loan->execute() && $stmt_update_book_qty->execute()) {
            logAudit($librarian_id, 'return_book', $loan_id, 'Book returned for loan ID ' . $loan_id);
            echo json_encode(['success' => true, 'message' => 'Book successfully returned.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error returning book: ' . $conn->error]);
        }
        $stmt_update_loan->close();
        $stmt_update_book_qty->close();
        break;

    case 'extend':
        $new_due_date = $data['new_due_date'] ?? '';
        if (empty($new_due_date) || !strtotime($new_due_date)) {
            echo json_encode(['success' => false, 'message' => 'Invalid new due date.']);
            exit();
        }
        $sql_extend = "UPDATE loans SET due_date = ?, status = 'borrowed' WHERE loan_id = ?";
        $stmt_extend = $conn->prepare($sql_extend);
        $stmt_extend->bind_param("si", $new_due_date, $loan_id);
        if ($stmt_extend->execute()) {
            logAudit($librarian_id, 'extend_due_date', $loan_id, 'Extended due date for loan ID ' . $loan_id . ' to ' . $new_due_date);
            echo json_encode(['success' => true, 'message' => 'Due date extended successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error extending due date: ' . $conn->error]);
        }
        $stmt_extend->close();
        break;

    case 'assess_penalty':
        $amount = $data['amount'] ?? 0;
        if (!is_numeric($amount) || $amount <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid penalty amount.']);
            exit();
        }

        // Check if a pending penalty already exists for this loan
        $sql_check_penalty = "SELECT penalty_id FROM penalties WHERE loan_id = ? AND status = 'pending'";
        $stmt_check_penalty = $conn->prepare($sql_check_penalty);
        $stmt_check_penalty->bind_param("i", $loan_id);
        $stmt_check_penalty->execute();
        $stmt_check_penalty->store_result();

        if ($stmt_check_penalty->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'A pending penalty already exists for this loan.']);
            $stmt_check_penalty->close();
            exit();
        }
        $stmt_check_penalty->close();

        // Get student_id for logging
        $sql_get_student = "SELECT student_id FROM loans WHERE loan_id = ?";
        $stmt_get_student = $conn->prepare($sql_get_student);
        $stmt_get_student->bind_param("i", $loan_id);
        $stmt_get_student->execute();
        $stmt_get_student->bind_result($student_id_for_penalty);
        $stmt_get_student->fetch();
        $stmt_get_student->close();

        $sql_assess = "INSERT INTO penalties (loan_id, amount, status, date_assessed) VALUES (?, ?, 'pending', CURDATE())";
        $stmt_assess = $conn->prepare($sql_assess);
        $stmt_assess->bind_param("id", $loan_id, $amount);
        if ($stmt_assess->execute()) {
            logAudit($librarian_id, 'assess_penalty', $loan_id, 'Assessed penalty of ' . $amount . ' for loan ID ' . $loan_id . ' to student ' . $student_id_for_penalty);
            echo json_encode(['success' => true, 'message' => 'Penalty assessed successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error assessing penalty: ' . $conn->error]);
        }
        $stmt_assess->close();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

$conn->close();
?>