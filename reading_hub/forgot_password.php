<?php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['role'] ?? null;
}

function redirectToLogin() {
    header("Location: login.php");
    exit();
}

function redirectToDashboard() {
    if (getUserRole() === 'librarian') {
        header("Location: librarian_dashboard.php");
    } else {
        header("Location: student_dashboard.php");
    }
    exit();
}

function logAudit($userId, $actionType, $targetId = null, $details = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action_type, target_id, details) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $actionType, $targetId, $details);
    $stmt->execute();
    $stmt->close();
}

// NEW HELPER FUNCTIONS FOR AI CHAT
function extractKeywords($message) {
    $common_words = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
    $words = array_diff(explode(' ', strtolower($message)), $common_words);
    return ['search' => implode(' ', $words)];
}

function extractBookName($message) {
    $words = explode(' ', $message);
    $common_verbs = ['find', 'search', 'look', 'get', 'check', 'see', 'want', 'need'];
    $filtered = array_diff($words, $common_verbs);
    return implode(' ', $filtered);
}

function getBookAvailability($book_query, $conn) {
    $sql = "SELECT b.book_id, b.title, a.author_name, b.quantity_available 
            FROM books b 
            LEFT JOIN authors a ON b.author_id = a.author_id 
            WHERE b.title LIKE ? OR a.author_name LIKE ?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $likeQuery = "%" . $book_query . "%";
    $stmt->bind_param("ss", $likeQuery, $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();
    $stmt->close();
    return $book;
}

function getUserLoans($user_id, $conn) {
    $sql = "SELECT l.loan_id, b.title, l.due_date, l.status 
            FROM loans l 
            JOIN books b ON l.book_id = b.book_id 
            WHERE l.user_id = ? 
            ORDER BY l.due_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $loans = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $loans;
}

function getUserPenalties($user_id, $conn) {
    $sql = "SELECT p.penalty_id, b.title, p.amount, p.status 
            FROM penalties p 
            JOIN loans l ON p.loan_id = l.loan_id 
            JOIN books b ON l.book_id = b.book_id 
            WHERE l.user_id = ? AND p.status != 'paid'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $penalties = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $penalties;
}

function getTopBorrowed($conn) {
    $sql = "SELECT b.title, COUNT(l.loan_id) as borrow_count 
            FROM loans l 
            JOIN books b ON l.book_id = b.book_id 
            GROUP BY b.book_id 
            ORDER BY borrow_count DESC 
            LIMIT 5";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $top_books = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $top_books;
}

function getOverdueReport($conn) {
    $sql = "SELECT u.full_name, b.title, l.due_date, 
            DATEDIFF(CURDATE(), l.due_date) as days_overdue 
            FROM loans l 
            JOIN books b ON l.book_id = b.book_id 
            JOIN users u ON l.user_id = u.user_id 
            WHERE l.status = 'overdue' 
            ORDER BY days_overdue DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    $overdue_books = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    $report = "Overdue Report:\n";
    foreach ($overdue_books as $book) {
        $report .= "- {$book['full_name']}: {$book['title']} ({$book['days_overdue']} days overdue)\n";
    }
    return $report;
}

function getPartnerLibraries($book_title = null) {
    global $conn;
    $libraries = [];
    
    $sql = "SELECT library_name AS name, address AS location, contact_number AS contact_info FROM partner_libraries";
    if ($book_title) {
        $sql .= " WHERE library_name LIKE ? OR address LIKE ?";
        $stmt = $conn->prepare($sql);
        $like_term = "%" . $book_title . "%";
        $stmt->bind_param("ss", $like_term, $like_term);
    } else {
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $libraries[] = $row;
    }
    $stmt->close();
    
    return $libraries;
}

// Assess and insert penalties for overdue loans (call this when checking loans)
function assessPenalties($conn) {
    $sql = "SELECT l.loan_id, l.due_date, l.user_id, p.penalty_id
            FROM loans l
            LEFT JOIN penalties p ON l.loan_id = p.loan_id AND p.status = 'pending'
            WHERE l.status = 'overdue' AND (p.penalty_id IS NULL OR p.amount = 0)";

    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $loan_id = $row['loan_id'];
            $user_id = $row['user_id'];
            $due_date = new DateTime($row['due_date']);
            $today = new DateTime();
            $days_overdue = max(0, $today->diff($due_date)->days);
            $penalty_amount = $days_overdue * 20.00;  // 20 PHP per day

            if ($penalty_amount > 0) {
                if ($row['penalty_id']) {
                    // Update existing penalty
                    $update_sql = "UPDATE penalties SET amount = ?, date_assessed = CURDATE() WHERE penalty_id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param("di", $penalty_amount, $row['penalty_id']);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    // Insert new penalty
                    $insert_sql = "INSERT INTO penalties (loan_id, user_id, amount, status, date_assessed) VALUES (?, ?, ?, 'pending', CURDATE())";
                    $stmt = $conn->prepare($insert_sql);
                    $stmt->bind_param("iid", $loan_id, $user_id, $penalty_amount);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
        $result->free();
    }
}

// Get penalties for a specific loan
function getLoanPenalties($loanId, $conn) {
    $sql = "SELECT SUM(amount) AS penalty FROM penalties WHERE loan_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $loanId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['penalty'] ?? 0;
}

// In functions.php, update the notifyLibrarian function:
function notifyLibrarian($message) {
    global $conn;
    
    // Get ALL librarians' user_ids
    $sql = "SELECT user_id FROM users WHERE role = 'librarian'";
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $librarian_id = $row['user_id'];
            $sql = "INSERT INTO notifications (user_id, type, message, date_sent) VALUES (?, 'password_change_request', ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $librarian_id, $message);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>