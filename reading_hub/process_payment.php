<?php
require_once 'config.php';
require_once 'functions.php';

session_start();
header('Content-Type: application/json');

// Check if user is logged in and is a student
if (!isLoggedIn() || getUserRole() !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_id = $_POST['loan_id'] ?? 0;
    $amount = $_POST['amount'] ?? 0;
    $user_id = $_SESSION['user_id'];

    error_log("Payment attempt - User: $user_id, Loan: $loan_id, Amount: $amount");

    if ($loan_id <= 0 || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid loan ID or amount.']);
        exit();
    }

    // Verify that the penalty belongs to the user and is pending
    $verify_sql = "SELECT p.penalty_id, p.amount 
                   FROM penalties p 
                   JOIN loans l ON p.loan_id = l.loan_id 
                   WHERE p.loan_id = ? AND l.user_id = ? AND p.status = 'pending'";
    $stmt = $conn->prepare($verify_sql);
    $stmt->bind_param("ii", $loan_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No pending penalty found for this loan.']);
        $stmt->close();
        exit();
    }
    
    $penalty_data = $result->fetch_assoc();
    $stmt->close();

    // Update penalty status to paid
    $update_sql = "UPDATE penalties SET status = 'paid', date_paid = NOW() WHERE loan_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $loan_id);

    if ($stmt->execute()) {
        // Log the payment
        logAudit($user_id, 'pay_penalty', $loan_id, 'Paid penalty of ₱' . number_format($amount, 2) . ' for loan ID: ' . $loan_id);
        
        error_log("Payment successful - User: $user_id, Loan: $loan_id");
        echo json_encode(['success' => true, 'message' => 'Payment processed successfully.']);
    } else {
        error_log("Payment failed - User: $user_id, Loan: $loan_id, Error: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Error processing payment: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

$conn->close();
?>