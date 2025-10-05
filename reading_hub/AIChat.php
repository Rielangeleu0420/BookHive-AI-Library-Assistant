<?php
require_once 'functions.php';

// Ensure user is logged in and is a student
if (!isLoggedIn() || getUserRole() !== 'student') {
    // This file is loaded via AJAX, so return an error message or empty content
    echo '<div class="ai-chat-message-bot"><div class="ai-chat-bubble ai-chat-bubble-bot">Unauthorized access. Please log in as a student.</div></div>';
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'];

// This file will handle both displaying the chat interface and processing AJAX requests for AI responses.
// For simplicity, initial load will just display the interface.
// Subsequent messages will be handled by an AJAX call to this same file with a 'message' parameter.

// Handle incoming AJAX message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    header('Content-Type: application/json');
    $user_message = trim($_POST['message']);

    // Simulate AI response logic
    $response_data = handleAIChatMessage($user_message, $user_id, $conn);
    echo json_encode($response_data);
    exit();
}

// Function to simulate AI response
function handleAIChatMessage($message, $user_id, $conn) {
    $lower_message = strtolower($message);
    $response = [
        'content' => "I'm sorry, I didn't understand that. Can you please rephrase or choose from the suggestions?",
        'suggestions' => [
            'Find a specific book',
            'Check book availability',
            'Find books elsewhere',
            'Get library directions'
        ]
    ];

    // Mock responses based on keywords
    $mock_responses = [
        'availability' => [
            'content' => 'I can help you check book availability! Here are some books currently available:\n\n📚 "Machine Learning Fundamentals" by Dr. Alex Kumar - Available (3 copies)\n📚 "Digital Signal Processing" by Maria Rodriguez - Available (2 copies)\n📚 "Modern Physics" by Robert Johnson - Checked out (next available: March 25)\n\n💡 Can\'t find what you need? I can suggest where to find it outside our library!',
            'suggestions' => ['Reserve a book', 'Search for specific title', 'Find books elsewhere', 'Browse by category']
        ],
        'recommendations' => [
            'content' => 'Based on your reading history in Computer Science, I recommend:\n\n⭐ "Advanced Algorithms" by Jennifer Lee - Perfect for building on your current knowledge\n⭐ "System Design Interview" by Alex Xu - Great for practical applications\n⭐ "Clean Code" by Robert Martin - Essential for software development',
            'suggestions' => ['Get more recommendations', 'Check availability', 'Add to reading list']
        ],
        'status' => [
            'content' => 'Here\'s your current borrowing status:\n\n📖 Current loans: 3 books\n⏰ Due soon: "Advanced Mathematics" (due tomorrow)\n🚨 Overdue: 1 book\n💰 Outstanding fines: ₱500\n📚 Total borrowed this semester: 8 books\n\nWould you like me to help you renew any books or pay fines?',
            'suggestions' => ['Renew books', 'View loan history', 'Pay fines', 'Check penalties']
        ],
        'interests' => [
            'content' => 'I notice you enjoy Computer Science and Mathematics! Here are some personalized suggestions:\n\n🎯 "Quantum Computing Explained" - New arrival, trending topic\n🎯 "Statistical Learning Theory" - Combines your interests\n🎯 "Artificial Intelligence: A Guide" - Popular with CS students',
            'suggestions' => ['Save to favorites', 'Get similar books', 'Set up alerts']
        ],
        'penalties' => [
            'content' => 'Here\'s your penalty information:\n\n💰 Total outstanding fines: ₱500\n📚 "Advanced Mathematics" - ₱500 (5 days overdue)\n\nPenalty rate: ₱100 per day\n\n💡 Tip: Return books on time to avoid future penalties. You can also set up due date reminders!',
            'suggestions' => ['Pay fines', 'Set reminders', 'Renew books', 'Contact librarian']
        ],
        'payment' => [
            'content' => 'I can help you with fine payments! Here are your options:\n\n💳 Online payment portal\n🏦 In-person at circulation desk\n📱 GCash or PayMaya\n💰 Bank transfer\n\nCurrent balance: ₱500\n\nWould you like me to guide you through the payment process?',
            'suggestions' => ['Pay online', 'Payment help', 'Contact librarian', 'View payment history']
        ],
        'fines' => [
            'content' => 'Let me check your current fines:\n\n💰 Outstanding Amount: ₱500\n📖 Overdue Book: "Advanced Mathematics"\n📅 Days Overdue: 5 days\n📊 Rate: ₱100 per day\n\n⚡ Quick actions available:',
            'suggestions' => ['Pay now', 'Request extension', 'View details', 'Contact support']
        ],
        'renew' => [
            'content' => 'I can help you renew your books! Here\'s what I found:\n\n📚 Eligible for renewal:\n• "Introduction to Computer Science" - Can extend 7 days\n• "Data Structures and Algorithms" - Can extend 7 days\n\n❌ Cannot renew:\n• "Advanced Mathematics" - Overdue (₱500 fine)\n\nWould you like me to proceed with the renewals?',
            'suggestions' => ['Renew eligible books', 'Pay fine first', 'Check renewal policy', 'Set reminders']
        ],
        'unavailable' => [
            'content' => 'This book is not available in our library right now. Here\'s where you can find it:\n\n🏪 **Physical Stores:**\n• National Book Store (nationwide branches)\n• Fully Booked (major malls)\n• Book Sale (discount bookstores)\n• Powerbooks (select locations)\n\n🌐 **Online Options:**\n• Shopee Philippines\n• Lazada Philippines\n• Amazon (international shipping)\n• Book Depository\n\n📚 **Academic Sources:**\n• Other university libraries\n• DLSU Library (partner institution)\n• Ateneo Library (reciprocal borrowing)\n\nWould you like me to help you with anything else?',
            'suggestions' => ['Find similar books', 'Reserve when available', 'Get store locations', 'Check partner libraries']
        ],
        'bookstores' => [
            'content' => 'Here are the best places to find books outside our library:\n\n🏬 **Major Bookstore Chains:**\n• National Book Store - Most comprehensive, 200+ branches nationwide\n• Fully Booked - Premium selection, major malls (BGC, Makati, QC)\n• Powerbooks - Academic and professional books\n• Book Sale - Discounted books, great for students\n\n🛒 **Online Marketplaces:**\n• Shopee Philippines - Wide selection, competitive prices\n• Lazada Philippines - Fast delivery, frequent sales\n• Carousell - Second-hand books from locals\n\n📍 **Specialty Stores:**\n• Bookmark (Makati) - Independent bookstore\n• Books for Less - Affordable options\n• Comic Odyssey - For graphic novels and comics\n\n💡 **Money-saving tips:** Check for student discounts, book fairs, and online promotions!',
            'suggestions' => ['Get store locations', 'Compare prices', 'Find textbook discounts', 'Check library partners']
        ],
        'notfound' => [
            'content' => 'I couldn\'t find that book in our collection. But don\'t worry! Here are your options:\n\n📋 **What I can do:**\n• Request the library to acquire this book\n• Suggest similar available titles\n• Help you find it at partner institutions\n• Guide you to external sources\n\n🏪 **Where to find it:**\n• National Book Store or our partner bookstores\n• Online platforms like Shopee or Lazada\n• Other university libraries (UP, DLSU, Ateneo)\n• Digital libraries (if available online)\n\n📝 **Next steps:**\n• Submit a book acquisition request\n• Check our interlibrary loan program\n• Browse our recommended alternatives',
            'suggestions' => ['Submit book request', 'Find similar books', 'Check other libraries', 'Browse alternatives']
        ],
        'partners' => [
            'content' => 'Our library has partnerships with several institutions where you can access books:\n\n🏫 **Academic Partners:**\n• De La Salle University Library\n• Ateneo de Manila University Library\n• University of the Philippines Library System\n• Miriam College Library\n\n🏪 **Bookstore Partners:**\n• National Book Store (10% student discount)\n• Fully Booked (special academic pricing)\n• Rex Book Store (textbook specialists)\n\n📚 **Digital Resources:**\n• EBSCO Academic databases\n• ProQuest research platform\n• Springer Nature eBooks\n• IEEE Xplore digital library\n\n💳 **How to access:**\n• Present your student ID\n• Some require library card registration\n• Digital resources available through campus network',
            'suggestions' => ['Get partner access', 'View digital resources', 'Check requirements', 'Contact librarian']
        ],
        'bookrequest' => [
            'content' => 'I can help you submit a book acquisition request! Here\'s how:\n\n📝 **Request Process:**\n• Fill out the book request form\n• Provide book details (title, author, ISBN)\n• Justify why it\'s needed for studies\n• Estimated processing time: 2-4 weeks\n\n📋 **Required Information:**\n• Complete bibliographic details\n• Course relevance\n• Number of students who might use it\n• Preferred format (print/digital)\n\n✅ **What happens next:**\n• Librarian reviews the request\n• Budget and relevance assessment\n• Approval notification via email\n• Book added to collection',
            'suggestions' => ['Submit request form', 'Check request status', 'View acquisition policy', 'Contact librarian']
        ],
        'directions' => [
            'content' => 'I can help you navigate our library! Here are the main sections:\n\n🗺️ **Library Map:**\n• Ground Floor: Circulation, New Arrivals, Magazines\n• 2nd Floor: Sciences, Mathematics, Engineering\n• 3rd Floor: Humanities, Literature, Arts\n• 4th Floor: Computer Science, IT, Research\n\n📍 **Finding Books:**\n• Use call numbers to locate books\n• Follow the shelf signs and colors\n• Ask library staff for assistance\n• Use our digital map on tablets\n\n🚶 **Navigation Tips:**\n• Books are arranged by Dewey Decimal System\n• Each section has clear signage\n• Study areas available on each floor',
            'suggestions' => ['Get specific directions', 'View digital map', 'Find study areas', 'Ask for help']
        ]
    ];

    // Check for specific book titles and simulate checking availability
    $is_book_query = str_contains($lower_message, 'book') && !str_contains($lower_message, 'recommendation') && !str_contains($lower_message, 'suggest');
    $book_titles = [
        'machine learning', 'algorithms', 'data structures', 'physics', 'mathematics',
        'chemistry', 'biology', 'history', 'literature', 'psychology', 'economics',
        'philosophy', 'computer science', 'engineering', 'calculus', 'statistics'
    ];

    $mentions_specific_book = false;
    foreach ($book_titles as $title) {
        if (str_contains($lower_message, $title)) {
            $mentions_specific_book = true;
            break;
        }
    }

    if ($is_book_query && $mentions_specific_book) {
        $unavailable_books = ['advanced calculus', 'quantum physics', 'organic chemistry', 'medieval history'];
        $is_unavailable = false;
        foreach ($unavailable_books as $book) {
            if (str_contains($lower_message, explode(' ', $book)[0]) || str_contains($lower_message, explode(' ', $book)[1])) {
                $is_unavailable = true;
                break;
            }
        }

        if ($is_unavailable) {
            $response = [
                'content' => "I checked our catalog and that book is currently not available in our library. Here's what I can do to help:\n\n📚 **Alternative Solutions:**\n• Check if we have similar books on the topic\n• Help you find it at external sources\n• Submit a book acquisition request\n• Access it through partner libraries\n\n🏪 **Where to find it:**\n• National Book Store - Most likely to have academic books\n• Fully Booked - Premium selection in major malls\n• Online: Shopee Philippines, Lazada Philippines\n• Academic partners: DLSU, Ateneo, UP libraries\n\n📋 **Next steps:**\n• I can search for similar available books\n• Guide you to the nearest bookstore\n• Help with interlibrary loan requests",
                'suggestions' => ['Find similar books', 'Get store locations', 'Submit book request', 'Check partner libraries']
            ];
        } else {
            $response = [
                'content' => "Great! I found that book in our collection:\n\n📚 **Book Status:**\n• Available: 2 copies on shelf\n• Location: Section B - Computer Science\n• Call number: QA76.73.C15\n• Can be borrowed for 14 days\n\n✅ **Quick actions:**\n• Reserve this book now\n• Get directions to the section\n• Check for related books\n• View reviews and ratings",
                'suggestions' => ['Reserve book', 'Get directions', 'Find related books', 'View details']
            ];
        }
    } elseif (str_contains($lower_message, 'availability') || str_contains($lower_message, 'available')) {
        $response = $mock_responses['availability'];
    } elseif (str_contains($lower_message, 'status') || str_contains($lower_message, 'loan') || str_contains($lower_message, 'borrow')) {
        $response = $mock_responses['status'];
    } elseif (str_contains($lower_message, 'interest') || str_contains($lower_message, 'favorite') || str_contains($lower_message, 'like')) {
        $response = $mock_responses['interests'];
    } elseif (str_contains($lower_message, 'recommend') || str_contains($lower_message, 'suggest')) {
        $response = $mock_responses['recommendations'];
    } elseif (str_contains($lower_message, 'penalties') || str_contains($lower_message, 'penalty') || str_contains($lower_message, 'fine')) {
        $response = $mock_responses['penalties'];
    } elseif (str_contains($lower_message, 'payment') || str_contains($lower_message, 'pay')) {
        $response = $mock_responses['payment'];
    } elseif (str_contains($lower_message, 'fines') || str_contains($lower_message, 'check my fines')) {
        $response = $mock_responses['fines'];
    } elseif (str_contains($lower_message, 'renew')) {
        $response = $mock_responses['renew'];
    } elseif (str_contains($lower_message, 'not available') || str_contains($lower_message, 'unavailable') || str_contains($lower_message, 'out of stock')) {
        $response = $mock_responses['unavailable'];
    } elseif (str_contains($lower_message, 'bookstore') || str_contains($lower_message, 'book store') || str_contains($lower_message, 'where to buy') || str_contains($lower_message, 'find books elsewhere')) {
        $response = $mock_responses['bookstores'];
    } elseif (str_contains($lower_message, 'not found') || str_contains($lower_message, 'can\'t find') || str_contains($lower_message, 'cannot find')) {
        $response = $mock_responses['notfound'];
    } elseif (str_contains($lower_message, 'partner') || str_contains($lower_message, 'other librar') || str_contains($lower_message, 'check other libraries')) {
        $response = $mock_responses['partners'];
    } elseif (str_contains($lower_message, 'submit book request') || str_contains($lower_message, 'book request') || str_contains($lower_message, 'acquire') || str_contains($lower_message, 'request book')) {
        $response = $mock_responses['bookrequest'];
    } elseif (str_contains($lower_message, 'directions') || str_contains($lower_message, 'where is') || str_contains($lower_message, 'navigate') || str_contains($lower_message, 'find section')) {
        $response = $mock_responses['directions'];
    }

    return $response;
}

// Initial chat messages for display when AIChat.php is loaded directly (via AJAX for the modal)
?>
<div class="ai-chat-quick-actions">
    <button class="ai-chat-quick-action-btn" onclick="sendAIChatMessage('Check book availability')">
        <i data-lucide="book-open"></i>
        <span>Check Availability</span>
    </button>
    <button class="ai-chat-quick-action-btn" onclick="sendAIChatMessage('I need help finding a specific book')">
        <i data-lucide="search"></i>
        <span>Find a Book</span>
    </button>
    <button class="ai-chat-quick-action-btn" onclick="sendAIChatMessage('Show my borrowing status')">
        <i data-lucide="clock"></i>
        <span>Borrowing Status</span>
    </button>
    <button class="ai-chat-quick-action-btn" onclick="sendAIChatMessage('Check my fines')">
        <i data-lucide="dollar-sign"></i>
        <span>Check Fines</span>
    </button>
    <button class="ai-chat-quick-action-btn" onclick="sendAIChatMessage('Where can I buy books?')">
        <i data-lucide="map-pin"></i>
        <span>Find Bookstores</span>
    </button>
    <button class="ai-chat-quick-action-btn" onclick="sendAIChatMessage('Check partner libraries')">
        <i data-lucide="external-link"></i>
        <span>Partner Libraries</span>
    </button>
</div>

<div id="ai-chat-messages-list" class="ai-chat-messages-list">
    <div class="ai-chat-message-container ai-chat-message-bot">
        <div class="ai-chat-avatar ai-chat-avatar-bot">
            <i data-lucide="bot" class="w-4 h-4"></i>
        </div>
        <div class="ai-chat-bubble ai-chat-bubble-bot">
            Hello <?php echo htmlspecialchars($user_name); ?>! I'm your AI library assistant. I can help you find specific books, check availability, manage loans, handle penalties, and guide you to external sources when books aren't available in our library. Just ask me about any book or library service!
            <span class="ai-chat-timestamp"><?php echo date('h:i A'); ?></span>
            <div class="ai-chat-suggestions">
                <span class="ai-chat-suggestion-badge" onclick="sendAIChatMessage('Find a specific book')">Find a specific book</span>
                <span class="ai-chat-suggestion-badge" onclick="sendAIChatMessage('Check book availability')">Check book availability</span>
                <span class="ai-chat-suggestion-badge" onclick="sendAIChatMessage('Find books elsewhere')">Find books elsewhere</span>
                <span class="ai-chat-suggestion-badge" onclick="sendAIChatMessage('Get library directions')">Get library directions</span>
            </div>
        </div>
    </div>
    <div class="ai-chat-messages-end"></div>
</div>

<div class="ai-chat-input-area">
    <input type="text" id="aiChatInput" class="ai-chat-input" placeholder="Ask me about books, loans, fines, or anything else..." onkeypress="if(event.keyCode === 13) sendAIChatMessage(this.value);" />
    <button id="aiChatSendBtn" class="ai-chat-send-btn" onclick="sendAIChatMessage(document.getElementById('aiChatInput').value)">
        <i data-lucide="send"></i>
    </button>
</div>

<script>
    const aiChatMessagesList = document.getElementById('ai-chat-messages-list');
    const aiChatInput = document.getElementById('aiChatInput');
    const aiChatSendBtn = document.getElementById('aiChatSendBtn');
    let isTyping = false;

    function appendMessage(type, content, suggestions = []) {
        const messageContainer = document.createElement('div');
        messageContainer.classList.add('ai-chat-message-container');
        messageContainer.classList.add(type === 'user' ? 'ai-chat-message-user' : 'ai-chat-message-bot');

        const avatar = document.createElement('div');
        avatar.classList.add('ai-chat-avatar');
        avatar.classList.add(type === 'user' ? 'ai-chat-avatar-user' : 'ai-chat-avatar-bot');
        avatar.innerHTML = `<i data-lucide="${type === 'user' ? 'user' : 'bot'}" class="w-4 h-4"></i>`;

        const bubble = document.createElement('div');
        bubble.classList.add('ai-chat-bubble');
        bubble.classList.add(type === 'user' ? 'ai-chat-bubble-user' : 'ai-chat-bubble-bot');
        bubble.innerHTML = `<p>${content.replace(/\n/g, '<br>')}</p><span class="ai-chat-timestamp">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>`;

        if (suggestions.length > 0) {
            const suggestionsDiv = document.createElement('div');
            suggestionsDiv.classList.add('ai-chat-suggestions');
            suggestions.forEach(suggestion => {
                const badge = document.createElement('span');
                badge.classList.add('ai-chat-suggestion-badge');
                badge.textContent = suggestion;
                badge.onclick = () => sendAIChatMessage(suggestion);
                suggestionsDiv.appendChild(badge);
            });
            bubble.appendChild(suggestionsDiv);
        }

        if (type === 'user') {
            messageContainer.appendChild(bubble);
            messageContainer.appendChild(avatar);
        } else {
            messageContainer.appendChild(avatar);
            messageContainer.appendChild(bubble);
        }

        aiChatMessagesList.appendChild(messageContainer);
        lucide.createIcons(); // Re-render Lucide icons for new messages
        scrollToBottom();
    }

    function showTypingIndicator() {
        if (isTyping) return;
        isTyping = true;
        const typingContainer = document.createElement('div');
        typingContainer.classList.add('ai-chat-message-container', 'ai-chat-message-bot');
        typingContainer.id = 'typing-indicator';

        const avatar = document.createElement('div');
        avatar.classList.add('ai-chat-avatar', 'ai-chat-avatar-bot');
        avatar.innerHTML = `<i data-lucide="bot" class="w-4 h-4"></i>`;

        const bubble = document.createElement('div');
        bubble.classList.add('ai-chat-bubble', 'ai-chat-bubble-bot');
        bubble.innerHTML = `
            <div class="ai-chat-typing-indicator">
                <div class="ai-chat-typing-dot"></div>
                <div class="ai-chat-typing-dot"></div>
                <div class="ai-chat-typing-dot"></div>
            </div>
        `;
        typingContainer.appendChild(avatar);
        typingContainer.appendChild(bubble);
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

    async function sendAIChatMessage(message) {
        if (!message.trim() || isTyping) return;

        appendMessage('user', message);
        aiChatInput.value = '';
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
            hideTypingIndicator();
            appendMessage('bot', result.content, result.suggestions);

        } catch (error) {
            console.error("Error sending message to AI Chat:", error);
            hideTypingIndicator();
            appendMessage('bot', "Oops! Something went wrong. Please try again later.", []);
        }
    }

    function scrollToBottom() {
        aiChatMessagesList.scrollTop = aiChatMessagesList.scrollHeight;
    }

    // Initial scroll to bottom when chat is loaded
    document.addEventListener('DOMContentLoaded', scrollToBottom);
</script>