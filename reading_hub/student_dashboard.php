<?php
require_once 'header.php';
// Load books from JSON file for AI recommendations
$available_books = [];
$books_json_path = 'book.json';

if (file_exists($books_json_path)) {
    $books_json_content = file_get_contents($books_json_path);
    $available_books = json_decode($books_json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $available_books = [];
    }
    
    // Filter only available books (quantity > 0)
    $available_books = array_filter($available_books, function($book) {
        return isset($book['quantity']) && $book['quantity'] > 0;
    });
    
    // Limit to 50 books
    $available_books = array_slice($available_books, 0, 50);
}

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

// Load books from JSON file for AI recommendations
$available_books = [];
$books_json_path = 'book.json';

if (file_exists($books_json_path)) {
    $books_json_content = file_get_contents($books_json_path);
    $available_books = json_decode($books_json_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        $available_books = [];
    }
    
    // Filter only available books (quantity > 0)
    $available_books = array_filter($available_books, function($book) {
        return isset($book['quantity']) && $book['quantity'] > 0;
    });
    
    // Limit to 50 books
    $available_books = array_slice($available_books, 0, 50);
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
                        <?php echo count(array_filter($current_loans, function($loan) { return $loan['status'] === 'overdue'; })); ?> overdue
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
                        <?php echo count(array_filter($current_loans, function($loan) { return getDaysUntilDue($loan['due_date']) <= 3 && $loan['status'] !== 'overdue'; })); ?>
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
                        <?php echo count(array_filter($current_loans, function($loan) { return $loan['status'] === 'overdue'; })); ?>
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
                        ₱<?php echo number_format($total_penalties, 2); ?>
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
                                            <?php if ($book['status'] === 'overdue'): ?>
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
                    <div class="card-description">Popular and newly added coastal treasures</div>
                </div>
                <div class="card-content">
                    <div class="space-y-4">
                        <?php foreach ($featured_books as $book): ?>
                            <div class="book-item cursor-pointer" 
                                 onclick="window.location.href='book_details.php?book_id=<?php echo $book['id']; ?>'">
                                <div class="book-cover">
                                    <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                                </div>
                                <div class="book-info">
                                    <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                                    <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
                                    <div class="book-meta">
                                        <div class="rating">
                                            <i data-lucide="star" class="w-3 h-3 fill-current text-warning"></i>
                                            <span><?php echo htmlspecialchars($book['rating']); ?></span>
                                        </div>
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
                    <button onclick="toggleAIChat()" class="action-btn action-btn-ai">
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

    <!-- AI Chat Modal -->
    <div id="aiChatModal" class="ai-chat-modal" style="display: none;">
        <div class="ai-chat-modal-content">
            <div class="ai-chat-modal-header">
                <div class="ai-chat-header-info">
                    <i data-lucide="bot" class="ai-chat-icon"></i>
                    <div class="ai-chat-header-text">
                        <h3>Library AI Assistant</h3>
                        <span class="ai-chat-status">Online • Ready to help</span>
                    </div>
                </div>
                <button class="ai-chat-close-btn" onclick="toggleAIChat()">&times;</button>
            </div>
            
            <div class="ai-chat-modal-body">
                <!-- Quick Action Buttons -->
                <div class="ai-quick-actions">
                    <button class="ai-quick-btn" onclick="sendQuickAction('book_recommendations')">
                        <i data-lucide="book-open"></i>
                        Book Recommendations
                    </button>
                    <button class="ai-quick-btn" onclick="sendQuickAction('check_availability')">
                        <i data-lucide="search"></i>
                        Check Availability
                    </button>
                    <button class="ai-quick-btn" onclick="sendQuickAction('manage_loans')">
                        <i data-lucide="book-marked"></i>
                        Manage Loans
                    </button>
                    <button class="ai-quick-btn" onclick="sendQuickAction('penalty_payments')">
                        <i data-lucide="dollar-sign"></i>
                        Penalty Payments
                    </button>
                    <button class="ai-quick-btn" onclick="sendQuickAction('external_sources')">
                        <i data-lucide="library"></i>
                        Find External Books
                    </button>
                    <button class="ai-quick-btn" onclick="sendQuickAction('help')">
                        <i data-lucide="help-circle"></i>
                        Help & Support
                    </button>
                </div>

                <!-- Chat Messages -->
                <div class="ai-chat-messages" id="chatMessages">
                    <div class="message assistant">
                        Hello! I'm your Library AI Assistant. How can I help you today? You can ask me about book recommendations, check availability, manage your loans, handle penalties, or find books from external sources.
                    </div>
                </div>

                <!-- Typing Indicator -->
                <div class="typing-indicator" id="typingIndicator">
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>

                <!-- Chat Input -->
                <div class="ai-chat-input-area">
                    <input type="text" class="ai-chat-input" id="chatInput" placeholder="Type your message here..." onkeypress="handleChatInput(event)">
                    <button class="ai-send-btn" onclick="sendMessage()">
                        <i data-lucide="send"></i>
                        Send
                    </button>
                </div>
            </div>
        </div>
    </div>

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

<script>
// Send message function
function sendMessage() {
    const input = document.getElementById('chatInput');
    const message = input.value.trim();
    
    if (message === '') return;
    
    // Add user message to chat
    addMessageToChat('user', message);
    input.value = '';
    
    // Show typing indicator
    showTypingIndicator();
    
    // Check if we should use local processing or send to AI PHP
    if (shouldUseLocalProcessing(message)) {
        // Process message locally (no internet needed)
        setTimeout(() => {
            processMessage(message);
        }, 1000);
    } else {
        // Send to AI PHP file
        sendToAIPHP(message);
    }
}

// Determine if we should use local processing
function shouldUseLocalProcessing(message) {
    const lowerMessage = message.toLowerCase();
    const localKeywords = [
        'recommend', 'suggest', 'available', 'availability', 
        'loan', 'borrow', 'current loan', 'penalty', 'fine', 
        'payment', 'external', 'bookstore', 'help', 'support',
        'children', 'kids', 'child', 'filipino', 'tagalog',
        'english', 'learning material', 'hello', 'hi', 'hey'
    ];
    
    return localKeywords.some(keyword => lowerMessage.includes(keyword));
}

// Send message to AI PHP file
function sendToAIPHP(message) {
    const formData = new FormData();
    formData.append('message', message);

    fetch('AIChat.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        hideTypingIndicator();
        
        if (data.content) {
            addMessageToChat('assistant', data.content);
        } else {
            addMessageToChat('assistant', 'I apologize, but I encountered an error. Please try again.');
        }
    })
    .catch(error => {
        hideTypingIndicator();
        console.error('Error:', error);
        // Fallback to local processing if AI PHP fails
        processMessage(message);
    });
}

// Handle Enter key in chat input
function handleChatInput(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

// Add message to chat UI
function addMessageToChat(sender, content) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${sender}`;
    
    if (typeof content === 'string') {
        // Convert line breaks to HTML
        const formattedContent = content.replace(/\n/g, '<br>');
        messageDiv.innerHTML = formattedContent;
    } else {
        messageDiv.innerHTML = content;
    }
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Show typing indicator
function showTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    indicator.style.display = 'flex';
    const chatMessages = document.getElementById('chatMessages');
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Hide typing indicator
function hideTypingIndicator() {
    const indicator = document.getElementById('typingIndicator');
    indicator.style.display = 'none';
}

// Generate book recommendations
function generateBookRecommendations() {
    if (booksData.length === 0) {
        return "I couldn't find any available books at the moment. Please check back later or ask a librarian for assistance.";
    }
    
    let response = "Based on our current collection, here are some books you might enjoy:\n\n";
    
    // Get random books for recommendations
    const shuffled = [...booksData].sort(() => 0.5 - Math.random());
    const selected = shuffled.slice(0, 5);
    
    selected.forEach(book => {
        const title = book.title || 'Unknown Title';
        const author = book.author || 'Unknown Author';
        const yearLevel = book.year_level || 'All Levels';
        const category = book.category || 'General';
        const quantity = book.quantity || 1;
        
        response += `<div class="book-recommendation">
            <strong>${title}</strong> by ${author}<br>
            <small>Level: ${yearLevel} • Category: ${category} • Available: ${quantity} copies</small>
        </div>`;
    });
    
    response += "\nYou can browse all available books by clicking 'Search Books' on your dashboard.";
    return response;
}

// Generate availability information
function generateAvailabilityInfo() {
    if (booksData.length === 0) {
        return "There are currently no books available in the library. Please check back later.";
    }
    
    const availableCount = booksData.length;
    const popularCategories = getPopularCategories();
    
    let response = `We currently have <strong>${availableCount} books</strong> available for borrowing. `;
    if (popularCategories.length > 0) {
        response += `Popular categories include: ${popularCategories.join(', ')}.\n\n`;
    }
    
    // Show some available books
    const recentBooks = booksData.slice(0, 3);
    response += "Some available books:\n";
    recentBooks.forEach(book => {
        const title = book.title || 'Unknown Title';
        const quantity = book.quantity || 1;
        
        response += `<div class="book-recommendation">
            • <strong>${title}</strong> - ${quantity} available
        </div>`;
    });
    
    response += "\nTo search for specific books, use the 'Search Books' feature on your dashboard.";
    return response;
}

// Generate loan information
function generateLoanInfo() {
    if (loansData.length === 0) {
        return "You currently have no active book loans. Feel free to browse our collection and borrow some books!";
    }
    
    let response = `You have <strong>${loansData.length} active loans</strong>:\n\n`;
    
    loansData.forEach(loan => {
        const daysUntilDue = getDaysUntilDue(loan.due_date);
        let status = '';
        
        if (loan.status === 'overdue') {
            status = '<span style="color: #DC3545;">OVERDUE</span>';
        } else if (daysUntilDue <= 3) {
            status = '<span style="color: #FFC107;">Due Soon</span>';
        } else {
            status = '<span style="color: #28A745;">On Track</span>';
        }
        
        response += `<div class="loan-status-item">
            <strong>${loan.title}</strong><br>
            Due: ${loan.due_date} • Status: ${status}
        </div>`;
    });
    
    const overdueCount = loansData.filter(loan => loan.status === 'overdue').length;
    if (overdueCount > 0) {
        response += `\nYou have <strong>${overdueCount} overdue book(s)</strong>. Please return them as soon as possible to avoid additional penalties.`;
    }
    
    return response;
}

// Generate penalty information
function generatePenaltyInfo() {
    const totalPenalty = studentData.total_penalties;
    
    if (totalPenalty === 0) {
        return "Great news! You have no outstanding penalties at the moment. Keep up the good work!";
    }
    
    let response = `You have <strong>₱${totalPenalty.toFixed(2)}</strong> in outstanding penalties.\n\n`;
    
    // Show penalty breakdown
    const overdueLoans = loansData.filter(loan => loan.status === 'overdue');
    if (overdueLoans.length > 0) {
        response += "Penalty breakdown:\n";
        overdueLoans.forEach(loan => {
            response += `<div class="loan-status-item">
                • <strong>${loan.title}</strong>: ₱${loan.penalty_amount.toFixed(2)}
            </div>`;
        });
    }
    
    response += `\nTo pay your penalties, please visit the library front desk during operating hours.`;
    return response;
}

// Generate external sources information
function generateExternalSources() {
    const externalSources = [
        "National Book Store (nationwide branches)",
        "Fully Booked (major malls)",
        "Powerbooks (academic and professional books)",
        "Book Sale (discounted books)",
        "Shopee Philippines (online)",
        "Lazada Philippines (online)"
    ];
    
    let response = "If you can't find what you're looking for in our library, here are some external sources where you might find books:\n\n";
    
    externalSources.forEach(source => {
        response += `<div class="book-recommendation">• ${source}</div>`;
    });
    
    response += "\nNote: Some of these resources may have different availability and pricing.";
    return response;
}

// Generate help information
function generateHelpInfo() {
    return `I can help you with various library tasks:\n\n
<strong>Book Recommendations</strong> - Get personalized book suggestions from our collection\n
<strong>Check Availability</strong> - See what books are currently available\n
<strong>Manage Loans</strong> - View your current borrowed books and due dates\n
<strong>Penalty Payments</strong> - Check and understand your penalties\n
<strong>External Sources</strong> - Find books from other bookstores and online sources\n\n
You can use the quick action buttons above or type your question directly. How can I assist you?`;
}

// Helper functions
function getPopularCategories() {
    const categories = {};
    booksData.forEach(book => {
        const category = book.category;
        if (category && category.trim() !== '') {
            categories[category] = (categories[category] || 0) + 1;
        }
    });
    
    return Object.keys(categories)
        .sort((a, b) => categories[b] - categories[a])
        .slice(0, 5);
}

function getDaysUntilDue(dueDate) {
    const due = new Date(dueDate);
    const today = new Date();
    const diffTime = due - today;
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

// Process message locally
function processMessage(message) {
    hideTypingIndicator();
    
    const lowerMessage = message.toLowerCase();
    let response = '';
    
    if (lowerMessage.includes('recommend') || lowerMessage.includes('suggest') || lowerMessage.includes('book recommendation')) {
        response = generateBookRecommendations();
    } 
    else if (lowerMessage.includes('available') || lowerMessage.includes('availability') || lowerMessage.includes('check book')) {
        response = generateAvailabilityInfo();
    }
    else if (lowerMessage.includes('loan') || lowerMessage.includes('borrow') || lowerMessage.includes('current loan')) {
        response = generateLoanInfo();
    }
    else if (lowerMessage.includes('penalty') || lowerMessage.includes('fine') || lowerMessage.includes('payment')) {
        response = generatePenaltyInfo();
    }
    else if (lowerMessage.includes('external') || lowerMessage.includes('other bookstore') || lowerMessage.includes('source') || lowerMessage.includes('where to buy')) {
        response = generateExternalSources();
    }
    else if (lowerMessage.includes('help') || lowerMessage.includes('support') || lowerMessage.includes('how to')) {
        response = generateHelpInfo();
    }
    else if (lowerMessage.includes('children') || lowerMessage.includes('kids') || lowerMessage.includes('child')) {
        response = generateChildrenBooks();
    }
    else if (lowerMessage.includes('filipino') || lowerMessage.includes('tagalog')) {
        response = generateFilipinoBooks();
    }
    else if (lowerMessage.includes('english') || lowerMessage.includes('learning material')) {
        response = generateEnglishBooks();
    }
    else if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
        response = "Hello " + studentData.name + "! I'm your Library Assistant. I can help you with:\n• Book recommendations\n• Checking availability\n• Managing your loans\n• Penalty information\n• Finding books from external sources\n\nWhat would you like to know?";
    }
    else {
        response = "I'm not sure I understand. I can help you with book recommendations, checking availability, managing loans, penalty information, and finding external book sources. What would you like to know?";
    }
    
    addMessageToChat('assistant', response);
}

// Additional specialized functions for your book categories
function generateChildrenBooks() {
    const childrenBooks = booksData.filter(book => 
        book.category && (
            book.category.includes("Children's Book") || 
            book.category.includes("Big Children") || 
            book.category.includes("Small Children")
        )
    );
    
    if (childrenBooks.length === 0) {
        return "I couldn't find any children's books available at the moment.";
    }
    
    let response = "Here are some available children's books:\n\n";
    
    const selected = childrenBooks.slice(0, 5);
    selected.forEach(book => {
        const title = book.title || 'Unknown Title';
        const author = book.author || 'Unknown Author';
        const category = book.category || 'Children';
        const quantity = book.quantity || 1;
        
        response += `<div class="book-recommendation">
            <strong>${title}</strong> by ${author}<br>
            <small>Category: ${category} • Available: ${quantity} copies</small>
        </div>`;
    });
    
    return response;
}

function generateFilipinoBooks() {
    const filipinoBooks = booksData.filter(book => 
        book.category && book.category.includes("Filipino")
    );
    
    if (filipinoBooks.length === 0) {
        return "I couldn't find any Filipino books available at the moment.";
    }
    
    let response = "Here are some available Filipino books:\n\n";
    
    const selected = filipinoBooks.slice(0, 5);
    selected.forEach(book => {
        const title = book.title || 'Unknown Title';
        const author = book.author || 'Unknown Author';
        const yearLevel = book.year_level || 'All Levels';
        const quantity = book.quantity || 1;
        
        response += `<div class="book-recommendation">
            <strong>${title}</strong> by ${author}<br>
            <small>Level: ${yearLevel} • Available: ${quantity} copies</small>
        </div>`;
    });
    
    return response;
}

function generateEnglishBooks() {
    const englishBooks = booksData.filter(book => 
        book.category && book.category.includes("English")
    );
    
    if (englishBooks.length === 0) {
        return "I couldn't find any English learning materials available at the moment.";
    }
    
    let response = "Here are some available English learning materials:\n\n";
    
    const selected = englishBooks.slice(0, 5);
    selected.forEach(book => {
        const title = book.title || 'Unknown Title';
        const author = book.author || 'Unknown Author';
        const yearLevel = book.year_level || 'All Levels';
        const quantity = book.quantity || 1;
        
        response += `<div class="book-recommendation">
            <strong>${title}</strong> by ${author}<br>
            <small>Level: ${yearLevel} • Available: ${quantity} copies</small>
        </div>`;
    });
    
    return response;
}
</script>

<?php
require_once 'footer.php';
?>
