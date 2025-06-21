<?php
session_start();
include 'config.php';
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

function askGeminiWithCurl($prompt, $apiKey) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyDrTUqi8QYHeflEoYZfm2mzcssehiO-Wk8';

    $data = [
        'contents' => [
            [
                'role' => 'user',
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    $jsonData = json_encode($data);
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return "Connection error occurred. Please try again.";
    }

    curl_close($ch);
    $responseData = json_decode($response, true);

    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        return $responseData['candidates'][0]['content']['parts'][0]['text'];
    } else {
        return "I'm having trouble processing your request right now. Please try again.";
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['job_description'])) {
    // Check if this is an AJAX request
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        
        $userInput = trim($_POST['job_description']);
        $your_api_key = "AIzaSyDrTUqi8QYHeflEoYZfm2mzcssehiO-Wk8";

        $defaultPrompt = <<<TEXT
You are a career assistant AI.

Given a job description, extract:
1. The top skills the applicant should highlight in their CV
2. Suggestions on how to prepare for the application or interview
3. Advice on what to do while waiting after applying

Format your response in this structure:

---
Key Skills to Highlight:
- ...
- ...

Preparation Tips:
- ...
- ...

What to Do While Waiting:
- ...
---

Job Description:
$userInput
TEXT;

        // If the input is not a job description, treat it as a free-form question
        $prompt = strpos(strtolower($userInput), 'responsibilities') !== false || strpos(strtolower($userInput), 'requirements') !== false
            ? $defaultPrompt
            : $userInput;

        $responseText = askGeminiWithCurl($prompt, $your_api_key);
        
        echo json_encode([
            'success' => true,
            'response' => $responseText,
            'user_message' => $userInput
        ]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Tracker - AI Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="./assets/css/ai_assistant.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="logo">
                <i class="fas fa-briefcase"></i>
                <span>JobTracker</span>
            </a>
            
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="add_job.php" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Job</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="ai_assistant.php" class="nav-link active">
                        <i class="fas fa-robot"></i>
                        <span>AI Assistant</span>
                    </a>
                </li>
            </ul>

            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</span>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-header">
            <h1>
                <i class="fas fa-robot"></i>
                AI Career Assistant
            </h1>
            <p>Get personalized career guidance and job application insights</p>
        </div>

        <div class="chat-container">
            <div class="chat-messages" id="chatMessages">
                <div class="welcome-message">
                    <div class="icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h2>Hello! I'm your AI Career Assistant</h2>
                    <p>Ask me about job applications, interview preparation, career advice, or paste a job description for personalized insights. I'm here to help you succeed in your career journey!</p>
                </div>
            </div>

            <div class="chat-input-area">
                <form id="chatForm">
                    <div class="input-container">
                        <div class="input-wrapper">
                            <textarea 
                                id="job_description" 
                                name="job_description" 
                                placeholder="Ask me anything about your career or paste a job description..."
                                required
                                rows="1"
                            ></textarea>
                            <button type="submit" class="send-button" id="sendButton" disabled>
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu functionality
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const navMenu = document.getElementById('navMenu');
        
        mobileMenuToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!navMenu.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            }
        });

        // Auto-resize textarea
        const textarea = document.getElementById('job_description');
        const sendButton = document.getElementById('sendButton');
        const chatMessages = document.getElementById('chatMessages');

        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, window.innerWidth < 768 ? 100 : 120) + 'px';
            
            // Enable/disable send button
            sendButton.disabled = this.value.trim() === '';
        });

        // Handle form submission with AJAX
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (textarea.value.trim() === '') {
                return;
            }

            const userMessage = textarea.value.trim();
            
            // Add user message
            addMessage(userMessage, 'user');
            
            // Clear textarea
            textarea.value = '';
            textarea.style.height = 'auto';
            sendButton.disabled = true;
            
            // Show typing indicator
            showTypingIndicator();
            
            // Send AJAX request to PHP
            const formData = new FormData();
            formData.append('job_description', userMessage);
            
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
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
                if (data.success && data.response) {
                    addMessage(data.response, 'assistant');
                } else {
                    addMessage("Sorry, I couldn't process your request. Please try again.", 'assistant');
                }
            })
            .catch(error => {
                hideTypingIndicator();
                addMessage("Sorry, there was an error connecting to the AI. Please check your internet connection and try again.", 'assistant');
                console.error('Error:', error);
            });
        });

        function addMessage(content, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${type}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.innerHTML = type === 'user' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';
            
            const messageContent = document.createElement('div');
            messageContent.className = 'message-content';
            
            // Format the content properly - preserve line breaks and formatting
            if (type === 'assistant') {
                messageContent.innerHTML = formatAIResponse(content);
            } else {
                messageContent.textContent = content;
            }
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(messageContent);
            
            // Remove welcome message if it exists
            const welcomeMessage = chatMessages.querySelector('.welcome-message');
            if (welcomeMessage) {
                welcomeMessage.remove();
            }
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function formatAIResponse(content) {
            // Convert line breaks to HTML
            content = content.replace(/\n/g, '<br>');
            
            // Make bold text bold
            content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            
            // Make italic text italic
            content = content.replace(/\*(.*?)\*/g, '<em>$1</em>');
            
            // Format bullet points
            content = content.replace(/^- (.+)$/gm, '• $1');
            
            return content;
        }

        function showTypingIndicator() {
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'typing-indicator';
            typingIndicator.id = 'typingIndicator';
            typingIndicator.innerHTML = `
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="typing-content">
                    <span>AI is thinking</span>
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            `;
            chatMessages.appendChild(typingIndicator);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // Handle Enter key (Shift+Enter for new line)
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim() !== '') {
                    document.getElementById('chatForm').dispatchEvent(new Event('submit'));
                }
            }
        });

        // Handle window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(function() {
                // Close mobile menu on resize
                navMenu.classList.remove('active');
                const icon = mobileMenuToggle.querySelector('i');
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            }, 250);
        });

        // Prevent zoom on iOS when focusing inputs
        if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
            const viewport = document.querySelector('meta[name=viewport]');
            viewport.setAttribute('content', viewport.getAttribute('content') + ', user-scalable=no');
        }

        // Initial state
        sendButton.disabled = textarea.value.trim() === '';
    </script>
</body>
</html>