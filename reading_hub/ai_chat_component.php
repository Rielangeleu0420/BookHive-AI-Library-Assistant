<?php
// AI Chat Component - Reusable modal for all pages
?>
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
                <p>Hello <?php echo htmlspecialchars($full_name ?? 'there'); ?>! I'm your AI library assistant. How can I help you today?</p>
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