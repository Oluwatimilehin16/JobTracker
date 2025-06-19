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
        echo 'cURL error: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    curl_close($ch);
    $responseData = json_decode($response, true);

    if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        return $responseData['candidates'][0]['content']['parts'][0]['text'];
    } else {
        return "Error from API: " . $response;
    }
}

$your_api_key = "AIzaSyDrTUqi8QYHeflEoYZfm2mzcssehiO-Wk8";

$responseText = "";
$userMessage = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['job_description'])) {
    $userInput = trim($_POST['job_description']);
    $userMessage = $userInput;

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
            
            <ul class="nav-menu">
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
                <span>Welcome, User!</span>
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
                <?php if (empty($userMessage) && empty($responseText)): ?>
                    <div class="welcome-message">
                        <div class="icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <h2>Hello! I'm your AI Career Assistant</h2>
                        <p>Ask me about job applications, interview preparation, career advice, or paste a job description for personalized insights. I'm here to help you succeed in your career journey!</p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($userMessage)): ?>
                    <div class="message user">
                        <div class="message-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($userMessage)) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($responseText)): ?>
                    <div class="message assistant">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <?= nl2br(htmlspecialchars($responseText)) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="chat-input-area">
                <form method="post" id="chatForm">
                    <div class="input-container">
                        <div class="input-wrapper">
                            <textarea 
                                id="job_description" 
                                name="job_description" 
                                placeholder="Ask me anything about your career or paste a job description..."
                                required
                                rows="1"
                            ><?= isset($_POST['job_description']) ? htmlspecialchars($_POST['job_description']) : '' ?></textarea>
                            <button type="submit" class="send-button" id="sendButton">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Auto-resize textarea
        const textarea = document.getElementById('job_description');
        const sendButton = document.getElementById('sendButton');
        const chatMessages = document.getElementById('chatMessages');

        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            
            // Enable/disable send button
            sendButton.disabled = this.value.trim() === '';
        });

        // Handle form submission
        document.getElementById('chatForm').addEventListener('submit', function(e) {
            if (textarea.value.trim() === '') {
                e.preventDefault();
                return;
            }
            
            // Show typing indicator
            showTypingIndicator();
        });

        function showTypingIndicator() {
            const typingIndicator = document.createElement('div');
            typingIndicator.className = 'typing-indicator';
            typingIndicator.innerHTML = `
                <i class="fas fa-robot"></i>
                <span>AI is thinking</span>
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            `;
            chatMessages.appendChild(typingIndicator);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Auto-scroll to bottom
        if (chatMessages.children.length > 1) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Handle Enter key (Shift+Enter for new line)
        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if (this.value.trim() !== '') {
                    document.getElementById('chatForm').submit();
                }
            }
        });

        // Initial state
        sendButton.disabled = textarea.value.trim() === '';
    </script>
</body>
</html>