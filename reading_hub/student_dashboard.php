<?php
require_once 'header.php';

// Redirect if not a student
if (getUserRole() !== 'student') {
    header("Location: librarian_dashboard.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'] ?? $_SESSION['username'];
$today = date('Y-m-d');

// Check for upcoming dues (due in 3 days or less)
$due_soon_sql = "SELECT COUNT(*) as due_soon_count FROM loans WHERE user_id = ? AND status = 'borrowed' AND due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
$stmt_due_soon = $conn->prepare($due_soon_sql);
$stmt_due_soon->bind_param("i", $student_id);
$stmt_due_soon->execute();
$due_soon_count = $stmt_due_soon->get_result()->fetch_assoc()['due_soon_count'];
$stmt_due_soon->close();

if ($due_soon_count > 0) {
    $check_sql = "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND type = 'due_soon' AND DATE(date_sent) = ?";
    $stmt_check = $conn->prepare($check_sql);
    $stmt_check->bind_param("is", $student_id, $today);
    $stmt_check->execute();
    if ($stmt_check->get_result()->fetch_row()[0] == 0) {
        $message = "You have $due_soon_count book(s) due soon (within 3 days).";
        $insert_sql = "INSERT INTO notifications (user_id, type, message) VALUES (?, 'due_soon', ?)";
        $stmt_insert = $conn->prepare($insert_sql);
        $stmt_insert->bind_param("is", $student_id, $message);
        $stmt_insert->execute();
        $stmt_insert->close();
    }
    $stmt_check->close();
}

// Assess penalties for overdue loans
assessPenalties($conn);

// Fetch current loans for the student, including penalties from the penalties table
// NOTE: Keeping pending_penalty fetch for display, but calculation ignores it (exact from my_loans.php)
$current_loans = [];
$sql_loans = "SELECT l.loan_id, b.title, a.author_name, l.due_date, l.status, 
                     (SELECT SUM(p.amount) FROM penalties p WHERE p.loan_id = l.loan_id AND p.status = 'pending') AS pending_penalty
              FROM loans l
              JOIN books b ON l.book_id = b.book_id
              LEFT JOIN authors a ON b.author_id = a.author_id
              WHERE l.user_id = ? AND (l.status = 'borrowed' OR l.status = 'overdue')
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

// EXACT PENALTY CALCULATION LOGIC FROM my_loans.php
foreach ($current_loans as &$loan) {
    $loan['is_overdue'] = (new DateTime($loan['due_date']) < new DateTime()) && $loan['status'] !== 'returned';
    
    // Calculate penalty amount (exact copy: always based on days overdue, ignoring DB)
    if ($loan['is_overdue']) {
        $due = new DateTime($loan['due_date']);
        $today = new DateTime();
        $days_overdue = max(0, $today->diff($due)->days);
        $loan['penalty_amount'] = $days_overdue * 20.00;  // 20 PHP per day
    } else {
        $loan['penalty_amount'] = 0;
    }
}
unset($loan);

// Calculate total penalties from the displayed data (exact from my_loans.php)
$total_penalties = 0;
foreach ($current_loans as $loan) {
    if ($loan['status'] !== 'returned' && $loan['penalty_amount'] > 0) {
        $total_penalties += $loan['penalty_amount'];
    }
}
$total_penalties = $total_penalties ?? 0;  // Fallback to 0

// ... (rest of the file remains unchanged, including borrowing history, notifications, popular books, etc.) ...

// Fetch borrowing history (returned books)
$borrowing_history = [];
$sql_history = "SELECT l.loan_id, b.title, a.author_name, l.borrow_date, l.return_date
                FROM loans l
                JOIN books b ON l.book_id = b.book_id
                LEFT JOIN authors a ON b.author_id = a.author_id
                WHERE l.user_id = ? AND l.status = 'returned'
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

// Fetch top 3 popular books based on borrow count
$popular_books_sql = "
    SELECT 
        b.book_id AS id,
        b.title,
        a.author_name AS author,
        g.genre_name AS category,
        b.quantity_available > 0 AS available,
        COUNT(l.loan_id) AS borrow_count
    FROM books b 
    LEFT JOIN loans l ON b.book_id = l.book_id 
    LEFT JOIN authors a ON b.author_id = a.author_id
    LEFT JOIN genres g ON b.genre_id = g.genre_id
    GROUP BY b.book_id 
    ORDER BY borrow_count DESC 
    LIMIT 3
";
$featured_books = $conn->query($popular_books_sql)->fetch_all(MYSQLI_ASSOC);

function getDaysUntilDue($dueDate) {
    $due = new DateTime($dueDate);
    $today = new DateTime();
    $interval = $today->diff($due);
    return (int)$interval->format('%R%a'); // Returns +days or -days
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - BookHive</title>
    <style>
        /* AI Chat Popup Styles */
        .ai-chat-popup {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 380px;
            height: 600px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            z-index: 10000;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        /* Header Section */
        .ai-chat-header {
            background: linear-gradient(135deg, #BD1B19, #A01513);
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 16px 16px 0 0;
        }

        .ai-chat-title {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .ai-chat-close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
        }

        .ai-chat-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Messages Area */
        .ai-chat-messages-list {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .ai-chat-message-container {
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }

        .ai-chat-message-user {
            flex-direction: row-reverse;
        }

        .ai-chat-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 600;
            font-size: 14px;
        }

        .ai-chat-avatar-bot {
            background: #3b82f6;
            color: white;
        }

        .ai-chat-avatar-user {
            background: #10b981;
            color: white;
        }

        .ai-chat-bubble {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 18px;
            position: relative;
            word-wrap: break-word;
            line-height: 1.4;
            font-size: 14px;
        }

        .ai-chat-bubble-bot {
            background: white;
            border: 1px solid #e5e7eb;
            color: #374151;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-bottom-left-radius: 4px;
        }

        .ai-chat-bubble-user {
            background: #3b82f6;
            color: white;
            border-bottom-right-radius: 4px;
        }

        .ai-chat-bubble p {
            margin: 0;
            white-space: pre-line;
        }

        .ai-chat-bubble strong {
            font-weight: 600;
        }

        .ai-chat-timestamp {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 6px;
            display: block;
            text-align: right;
        }

        .ai-chat-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .ai-chat-suggestion-badge {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 16px;
            padding: 6px 12px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            color: #374151;
            font-weight: 500;
        }

        .ai-chat-suggestion-badge:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        /* Input Area */
        .ai-chat-input-area {
            display: flex;
            gap: 12px;
            padding: 16px;
            border-top: 1px solid #e5e7eb;
            background: white;
            align-items: flex-end;
        }

        #aiChatInput {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 20px;
            padding: 12px 16px;
            resize: none;
            font-family: inherit;
            font-size: 14px;
            line-height: 1.5;
            max-height: 120px;
            outline: none;
            transition: border-color 0.2s;
            background: #f9fafb;
        }

        #aiChatInput:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: white;
        }

        #aiChatSendBtn {
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            flex-shrink: 0;
            font-size: 16px;
        }

        #aiChatSendBtn:hover {
            background: #2563eb;
            transform: scale(1.05);
        }

        /* Typing Indicator */
        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 12px 16px;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dots span {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #6b7280;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes typing {
            0%, 80%, 100% { 
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% { 
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Scrollbar styling */
        .ai-chat-messages-list::-webkit-scrollbar {
            width: 6px;
        }

        .ai-chat-messages-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .ai-chat-messages-list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .ai-chat-messages-list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Quick Actions */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 16px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .action-button {
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 13px;
            color: #374151;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
            font-weight: 500;
        }

        .action-button:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
            transform: translateY(-1px);
        }

        /* Floating Chat Button */
        .chat-button-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 9999;
        }

        .chat-btn {
            background: linear-gradient(135deg, #BD1B19, #A01513);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 15px 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(189, 27, 25, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .chat-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(189, 27, 25, 0.4);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Main Content -->
        <main class="p-6 space-y-6">
            <!-- Welcome Section -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-primary mb-2">Welcome back, <?php echo htmlspecialchars(explode(' ', $full_name)[0]); ?>! 🌊</h1>
                    <p class="text-secondary text-lg">
                        Explore your digital library with AI-powered assistance and discover new knowledge
                    </p>
                </div>
                <a href="books_available.php" class="btn btn-info">
                    <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                    Browse Books
                </a>
            </div>

            <!-- Quick Stats -->
            <div class="grid gap-6 md:grid-cols-5">
                <!-- Current Loans Card -->
                <div class="card stat-card-1">
                    <div class="card-header">
                        <div class="card-title">Current Loans</div>
                        <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center">
                            <i data-lucide="book-marked" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-primary mb-1"><?php echo count($current_loans); ?></div>
                        <p class="text-sm text-secondary">
                            <?php echo count(array_filter($current_loans, function($loan) { return $loan['is_overdue']; })); ?> overdue
                        </p>
                    </div>
                </div>
                
                <!-- Books Read Card -->
                <div class="card stat-card-2">
                    <div class="card-header">
                        <div class="card-title">Books Read</div>
                        <div class="w-10 h-10 bg-secondary rounded-xl flex items-center justify-center">
                            <i data-lucide="book-open" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-primary mb-1"><?php echo count($borrowing_history); ?></div>
                        <p class="text-sm text-secondary">
                            This semester
                        </p>
                    </div>
                </div>
                
                <!-- Due Soon Card -->
                <div class="card stat-card-3">
                    <div class="card-header">
                        <div class="card-title">Due Soon</div>
                        <div class="w-10 h-10 bg-accent rounded-xl flex items-center justify-center">
                            <i data-lucide="clock" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-primary mb-1">
                            <?php echo count(array_filter($current_loans, function($loan) { return getDaysUntilDue($loan['due_date']) <= 3 && !$loan['is_overdue']; })); ?>
                        </div>
                        <p class="text-sm text-secondary">
                            Within 3 days
                        </p>
                    </div>
                </div>
                
                <!-- Overdue Card -->
                <div class="card stat-card-4">
                    <div class="card-header">
                        <div class="card-title">Overdue</div>
                        <div class="w-10 h-10 bg-danger rounded-xl flex items-center justify-center">
                            <i data-lucide="alert-triangle" class="h-5 w-5 text-white"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="text-3xl font-bold text-danger mb-1">
                            <?php echo count(array_filter($current_loans, function($loan) { return $loan['is_overdue']; })); ?>
                        </div>
                        <p class="text-sm text-secondary">
                            Needs attention
                        </p>
                    </div>
                </div>
                
                <!-- Penalties Card -->
                <div class="card stat-card-5">
					<div class="card-header">
						<div class="card-title">Penalties</div>
						<div class="w-10 h-10 bg-success rounded-xl flex items-center justify-center">
							<span class="text-white font-bold text-lg">₱</span>
						</div>
					</div>
					<div class="card-content">
						<div class="text-3xl font-bold text-success mb-1">
							₱<?php echo number_format($total_penalties, 2); ?>  <!-- FIXED: Now shows correct total, e.g., ₱120 for 6 days -->
						</div>
						<p class="text-sm text-secondary">
							Outstanding fees
						</p>
					</div>
				</div>
            </div>

            <div class="grid gap-8 md:grid-cols-2">
                <!-- Current Loans Section -->
                <div class="section-card">
                    <div class="card-header bg-gradient-to-r">
                        <div class="card-title text-xl flex items-center">
                            <i data-lucide="book-marked" class="w-5 h-5 mr-2"></i>
                            Current Loans
                        </div>
                        <div class="card-description">Books you currently have borrowed</div>
                    </div>
                    <div class="card-content">
                        <div class="space-y-4">
                            <?php if (!empty($current_loans)): ?>
                                <?php foreach ($current_loans as $book): ?>
                                    <?php
                                    $daysUntilDue = getDaysUntilDue($book['due_date']);
                                    $penalty = $book['penalty_amount'];
                                    ?>
                                    <div class="book-item">
                                        <div class="book-cover">
                                            <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                                        </div>
                                        <div class="book-info">
                                            <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                            <div class="book-author"><?php echo htmlspecialchars($book['author_name'] ?? 'N/A'); ?></div>
                                            <div class="book-meta">
                                                <div class="meta-item">
                                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                                    <span>Due: <?php echo htmlspecialchars($book['due_date']); ?></span>
                                                </div>
                                                <?php if ($book['is_overdue']): ?>
                                                    <span class="status-badge badge-overdue">Overdue</span>
                                                    <span class="status-badge badge-overdue">Fine: ₱<?php echo number_format($penalty, 2); ?></span>
                                                <?php elseif ($daysUntilDue <= 3 && $daysUntilDue >= 0): ?>
                                                    <span class="status-badge badge-due-soon">Due Soon</span>
                                                <?php else: ?>
                                                    <span class="status-badge badge-available">On Time</span>
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

                <!-- Featured Books Section -->
                <div class="section-card">
                    <div class="card-header bg-gradient-to-r">
                        <div class="card-title text-xl flex items-center">
                            <i data-lucide="star" class="w-5 h-5 mr-2"></i>
                            Featured Books
                        </div>
                        <div class="card-description">Popular and newly added</div>
                    </div>
                    <div class="card-content">
                        <div class="space-y-4">
                            <?php foreach ($featured_books as $book): ?>
                                <div class="book-item">
                                    <div class="book-cover">
                                        <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                                    </div>
                                    <div class="book-info">
                                        <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                        <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                        <div class="book-meta">
                                            <span class="status-badge badge-category">
                                                <?php echo htmlspecialchars($book['category']); ?>
                                            </span>
                                            <?php if ($book['available']): ?>
                                                <span class="status-badge badge-available">Available</span>
                                            <?php else: ?>
                                                <span class="status-badge badge-checked-out">Checked Out</span>
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
            <div class="section-card quick-actions">
                <div class="card-header">
                    <div class="card-title text-xl">⚡ Quick Actions</div>
                    <div class="card-description">Common tasks and AI-powered shortcuts</div>
                </div>
                <div class="card-content">
                    <div class="action-buttons">
                        <a href="books_available.php" class="action-btn action-btn-search">
                            <i data-lucide="search" class="w-4 h-4"></i>
                            Search Books
                        </a>
                        <button class="action-btn action-btn-ai" onclick="toggleAIChat()">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                            AI Assistant
                        </button>
                        <a href="my_loans.php" class="action-btn action-btn-loans">
                            <i data-lucide="book-marked" class="w-4 h-4"></i>
                            My Loans
                        </a>
                        <a href="my_loans.php" class="action-btn action-btn-history">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            Loan History
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- AI Chat Popup (initially hidden) -->
    <div id="aiChatPopup" class="ai-chat-popup" style="display: none;">
        <!-- Header Section -->
        <div class="ai-chat-header">
            <div class="ai-chat-title">
                <i data-lucide="bot"></i>
                <span>AI Library Assistant</span>
            </div>
            <button class="ai-chat-close-btn" onclick="closeAIChat()">
                &times;
            </button>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions-grid">
            <button type="button" class="action-button" data-message="Find a Book">Find a Book</button>
            <button type="button" class="action-button" data-message="Borrowing Status">Borrowing Status</button>
            <button type="button" class="action-button" data-message="Check fines">Check fines</button>
            <button type="button" class="action-button" data-message="Partner Libraries">Partner Libraries</button>
        </div>

        <!-- Messages Area -->
        <div id="ai-chat-messages-list" class="ai-chat-messages-list">
            <!-- Initial Bot Message -->
            <div class="ai-chat-message-container ai-chat-message-bot">
                <div class="ai-chat-avatar ai-chat-avatar-bot">
                    AI
                </div>
                <div class="ai-chat-bubble ai-chat-bubble-bot">
                    <p>Hello <?php echo htmlspecialchars($full_name); ?>! I'm your AI library assistant. How can I help you today?</p>
                    <span class="ai-chat-timestamp"><?php echo date('h:i A'); ?></span>
                    
                    <div class="ai-chat-suggestions">
                        <span class="ai-chat-suggestion-badge" data-message="Find a specific book">Find a book</span>
                        <span class="ai-chat-suggestion-badge" data-message="Check book availability">Check availability</span>
                        <span class="ai-chat-suggestion-badge" data-message="My loans">My loans</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="ai-chat-input-area">
            <textarea id="aiChatInput" placeholder="Ask me about books, loans, fines..." rows="1"></textarea>
            <button id="aiChatSendBtn" type="button">
                ↑
            </button>
        </div>
    </div>

    <!-- Floating AI Chat Button -->
    <div class="chat-button-container">
        <button class="chat-btn" onclick="toggleAIChat()">💬 AI Chat</button>
    </div>

    <script>
        // AI Chat functionality
        let isTyping = false;
        let aiChatInitialized = false;

        function toggleAIChat() {
            const popup = document.getElementById('aiChatPopup');
            if (popup.style.display === 'none') {
                popup.style.display = 'flex';
                if (!aiChatInitialized) {
                    initializeAIChat();
                    aiChatInitialized = true;
                }
                document.getElementById('aiChatInput').focus();
            } else {
                popup.style.display = 'none';
            }
        }

        function closeAIChat() {
            document.getElementById('aiChatPopup').style.display = 'none';
        }

        function initializeAIChat() {
            console.log('AI Chat Popup initialized');
            
            const aiChatMessagesList = document.getElementById('ai-chat-messages-list');
            const aiChatInput = document.getElementById('aiChatInput');
            const aiChatSendBtn = document.getElementById('aiChatSendBtn');

            // Setup all event listeners
            function setupEventListeners() {
                // Send button click
                aiChatSendBtn.addEventListener('click', function() {
                    const message = aiChatInput.value.trim();
                    if (message) {
                        sendAIChatMessage(message);
                    }
                });

                // Enter key in input
                aiChatInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        const message = aiChatInput.value.trim();
                        if (message) {
                            sendAIChatMessage(message);
                        }
                    }
                });

                // Auto-resize textarea
                aiChatInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });

                // Action buttons
                document.querySelectorAll('.action-button').forEach(button => {
                    button.addEventListener('click', function() {
                        const message = this.getAttribute('data-message');
                        sendAIChatMessage(message);
                    });
                });

                // Suggestion badges
                document.querySelectorAll('.ai-chat-suggestion-badge').forEach(badge => {
                    badge.addEventListener('click', function() {
                        const message = this.getAttribute('data-message');
                        sendAIChatMessage(message);
                    });
                });
            }

            function appendMessage(type, content, suggestions = []) {
                const messageContainer = document.createElement('div');
                messageContainer.classList.add('ai-chat-message-container', `ai-chat-message-${type}`);
                
                const avatar = document.createElement('div');
                avatar.classList.add('ai-chat-avatar', `ai-chat-avatar-${type}`);
                avatar.textContent = type === 'user' ? 'You' : 'AI';
                
                const bubble = document.createElement('div');
                bubble.classList.add('ai-chat-bubble', `ai-chat-bubble-${type}`);
                
                // Format content with line breaks and bold text
                const formattedContent = content
                    .replace(/\n/g, '<br>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    
                // Get current time in 12-hour format
                const now = new Date();
                const timeString = now.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                });
                    
                bubble.innerHTML = `<p>${formattedContent}</p><span class="ai-chat-timestamp">${timeString}</span>`;
                
                // Add suggestions if any
                if (suggestions.length > 0) {
                    const suggestionsDiv = document.createElement('div');
                    suggestionsDiv.classList.add('ai-chat-suggestions');
                    suggestions.forEach(suggestion => {
                        const badge = document.createElement('span');
                        badge.classList.add('ai-chat-suggestion-badge');
                        badge.textContent = suggestion;
                        badge.setAttribute('data-message', suggestion);
                        badge.addEventListener('click', function() {
                            sendAIChatMessage(suggestion);
                        });
                        suggestionsDiv.appendChild(badge);
                    });
                    bubble.appendChild(suggestionsDiv);
                }
                
                messageContainer.appendChild(avatar);
                messageContainer.appendChild(bubble);
                aiChatMessagesList.appendChild(messageContainer);
                
                scrollToBottom();
            }

            async function sendAIChatMessage(message) {
                if (!message || !message.trim()) {
                    console.log('Empty message, skipping');
                    return;
                }
                
                if (isTyping) {
                    console.log('Already typing, please wait');
                    return;
                }
                
                console.log('Sending message:', message);
                
                // Add user message to chat
                appendMessage('user', message);
                aiChatInput.value = '';
                aiChatInput.style.height = 'auto';
                
                // Show typing indicator
                showTypingIndicator();
                
                try {
                    const response = await fetch('AIChat.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `message=${encodeURIComponent(message)}`
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const result = await response.json();
                    console.log('Response received:', result);
                    
                    hideTypingIndicator();
                    
                    if (result.content) {
                        appendMessage('bot', result.content, result.suggestions || []);
                    } else {
                        appendMessage('bot', 'I apologize, but I encountered an issue. Please try again.', []);
                    }
                    
                } catch (error) {
                    console.error('Error sending message:', error);
                    hideTypingIndicator();
                    appendMessage('bot', 'Sorry, I am having trouble connecting. Please check your internet connection and try again.', []);
                }
            }

            function showTypingIndicator() {
                if (isTyping) return;
                
                isTyping = true;
                const typingContainer = document.createElement('div');
                typingContainer.id = 'typing-indicator';
                typingContainer.classList.add('ai-chat-message-container', 'ai-chat-message-bot');
                
                typingContainer.innerHTML = `
                    <div class="ai-chat-avatar ai-chat-avatar-bot">
                        AI
                    </div>
                    <div class="ai-chat-bubble ai-chat-bubble-bot typing-indicator">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                `;
                
                aiChatMessagesList.appendChild(typingContainer);
                scrollToBottom();
            }

            function hideTypingIndicator() {
                isTyping = false;
                const typingIndicator = document.getElementById('typing-indicator');
                if (typingIndicator) {
                    typingIndicator.remove();
                }
            }

            function scrollToBottom() {
                if (aiChatMessagesList) {
                    aiChatMessagesList.scrollTop = aiChatMessagesList.scrollHeight;
                }
            }

            // Initialize
            setupEventListeners();
            scrollToBottom();

            // Make functions available globally
            window.sendAIChatMessage = sendAIChatMessage;
        }

        // Initialize Lucide icons when document is ready
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>

    <?php
    require_once 'footer.php';
    ?>
</body>
</html>