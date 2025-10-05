    </div> <!-- End of main-content -->
    <footer>
        Donated by Mapúa University Information Technology Students
    </footer>

    <?php if ($current_role === 'student'): ?>
        <!-- AI Chatbot Button for Students -->
        <button class="ai-chat-fab" onclick="toggleAIChat()">
            <i data-lucide="message-circle"></i>
            <span>AI Chat</span>
        </button>

        <!-- AI Chatbot Modal (will be loaded via AJAX) -->
        <div id="aiChatModal" class="ai-chat-modal" style="display: none;">
            <div class="ai-chat-content">
                <div class="ai-chat-header">
                    <i data-lucide="bot" class="ai-chat-icon"></i>
                    <span class="ai-chat-title">AI-Powered Library Assistant</span>
                    <button class="ai-chat-close-btn" onclick="toggleAIChat()">&times;</button>
                </div>
                <div class="ai-chat-description">
                    Chat with your AI-powered library assistant to get book recommendations, check availability, manage loans, handle penalty payments, and find books at external sources when unavailable.
                </div>
                <div id="aiChatBody" class="ai-chat-body">
                    <!-- Chat messages will be loaded here -->
                </div>
            </div>
        </div>

        <script>
            let aiChatOpen = <?php echo json_encode($showAIChat); ?>;
            const aiChatModal = document.getElementById('aiChatModal');
            const aiChatBody = document.getElementById('aiChatBody');

            function toggleAIChat() {
                aiChatOpen = !aiChatOpen;
                if (aiChatOpen) {
                    aiChatModal.style.display = 'flex';
                    loadAIChatContent();
                } else {
                    aiChatModal.style.display = 'none';
                }
            }

            async function loadAIChatContent() {
                if (aiChatBody.innerHTML === '' || aiChatOpen) { // Only load if empty or forced open
                    try {
                        const response = await fetch('AIChat.php');
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const html = await response.text();
                        aiChatBody.innerHTML = html;
                        // Re-initialize Lucide icons after content is loaded
                        lucide.createIcons();
                        // Scroll to bottom after loading
                        const messagesEndRef = document.querySelector('.ai-chat-messages-end');
                        if (messagesEndRef) {
                            messagesEndRef.scrollIntoView({ behavior: 'smooth' });
                        }
                    } catch (error) {
                        console.error("Failed to load AI Chat content:", error);
                        aiChatBody.innerHTML = "<p>Error loading chat. Please try again.</p>";
                    }
                }
            }

            // Open AI Chat on page load if showAIChat is true
            if (aiChatOpen) {
                toggleAIChat();
            }
        </script>
    <?php endif; ?>
</body>
</html>