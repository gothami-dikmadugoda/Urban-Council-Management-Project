<?php
session_start();
require_once __DIR__ . '/../controllers/ChatController.php';
require_once __DIR__ . '/../controllers/AdminController.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$chatController = new ChatController();
$adminController = new AdminController();

// Get user's groups and staff list
$groups = $chatController->getUserGroups($_SESSION['user_id']);
$staffList = $adminController->getAllStaff();

// Initialize department groups if needed
$chatController->initializeDepartmentGroups();

// Update user's online status
$chatController->updateUserStatus($_SESSION['user_id'], 'online');

// Register offline status on window unload
register_shutdown_function(function() use ($chatController) {
    $chatController->updateUserStatus($_SESSION['user_id'], 'offline');
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat System - Urban Council</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/emoji-mart@latest/css/emoji-mart.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #7C4585;
            --secondary-color: #23283a;
            --accent-color: #C95792;
            --text-muted: #bfc2c7;
            --border-color: #393e4a;
            --hover-bg: #2d3142;
            --active-bg: #3D365C;
            --message-sent-bg: #C95792;
            --message-received-bg: #23283a;
            --online-status: #31a24c;
            --offline-status: #65676b;
            --notification-bg: #F8B55F;
        }

        body {
            background: linear-gradient(135deg, #151921 0%, #23283a 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #F1F1F1;
        }

        .chat-container {
            height: calc(100vh - 56px);
            margin-top: 56px;
            background: #202632;
            box-shadow: 0 8px 32px rgba(61, 54, 92, 0.18);
            border-radius: 18px;
            overflow: hidden;
            padding: 1.5rem 1.5rem 1.2rem 1.5rem;
        }

        .chat-sidebar {
            height: 100%;
            border-right: 1px solid var(--border-color);
            background: #23283a;
            color: #F1F1F1;
            padding: 2rem 1.2rem 2rem 1.2rem;
            border-radius: 16px 0 0 16px;
        }

        .chat-list {
            height: calc(100% - 60px);
            overflow-y: auto;
            scrollbar-width: thin;
            padding-bottom: 1.5rem;
            margin-top: 1rem;
        }

        .chat-list::-webkit-scrollbar {
            width: 6px;
        }

        .chat-list::-webkit-scrollbar-thumb {
            background-color: var(--text-muted);
            border-radius: 3px;
        }

        .chat-messages {
            height: calc(100% - 140px);
            overflow-y: auto;
            padding: 36px 40px 28px 40px;
            background: #202632;
            color: #F1F1F1;
            scrollbar-width: thin;
            border-radius: 0 0 18px 18px;
        }

        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background-color: var(--text-muted);
            border-radius: 3px;
        }

        .message-input {
            border-top: 1px solid var(--border-color);
            padding: 22px 32px 22px 32px;
            background: #23283a;
            width: 100%;
            box-shadow: 0 -2px 10px rgba(61, 54, 92, 0.10);
            border-radius: 0 0 18px 18px;
            position: static;
            flex-shrink: 0;
        }

        .chat-message {
            margin-bottom: 15px;
            max-width: 80%;
            position: relative;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message-sent {
            margin-left: auto;
            background: linear-gradient(135deg, #C95792, #7C4585);
            color: #fff;
            border-radius: 18px 18px 4px 18px;
            padding: 12px 20px;
            box-shadow: 0 2px 8px rgba(201, 87, 146, 0.10);
        }

        .message-received {
            margin-right: auto;
            background: #23283a;
            color: #F1F1F1;
            border-radius: 18px 18px 18px 4px;
            padding: 12px 20px;
            box-shadow: 0 2px 8px rgba(61, 54, 92, 0.10);
        }

        .chat-list-item {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            position: relative;
            border-radius: 14px;
            margin: 0 0 14px 0;
        }

        .chat-list-item:hover {
            background: var(--hover-bg);
            color: #fff;
        }

        .chat-list-item.active {
            background: var(--active-bg);
            color: #fff;
        }

        .chat-header {
            padding: 28px 32px 18px 32px;
            border-bottom: 1px solid var(--border-color);
            background: #23283a;
            color: #F1F1F1;
            box-shadow: 0 2px 8px rgba(61, 54, 92, 0.10);
            border-radius: 18px 18px 0 0;
            margin-bottom: 0.5rem;
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            margin-right: 16px;
            background: #393e4a;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: transform 0.2s ease;
            font-size: 1.3rem;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: #F1F1F1;
            margin-bottom: 2px;
        }

        .user-status {
            font-size: 0.8em;
            color: var(--text-muted);
        }

        .online-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            position: absolute;
            bottom: 0;
            right: 0;
            border: 2px solid white;
            transition: background-color 0.3s ease;
        }

        .online-status.online {
            background-color: var(--online-status);
        }

        .online-status.offline {
            background-color: var(--offline-status);
        }

        .message-actions {
            display: none;
            position: absolute;
            right: 0;
            top: 0;
            background: white;
            border-radius: 20px;
            padding: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }

        .chat-message:hover .message-actions {
            display: flex;
            animation: fadeIn 0.2s ease-in-out;
        }

        .action-button {
            background: none;
            border: none;
            padding: 6px 10px;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: 15px;
            transition: all 0.2s ease;
        }

        .action-button:hover {
            color: var(--accent-color);
            background-color: var(--hover-bg);
        }

        .message-input form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .message-input input {
            border-radius: 20px;
            padding: 10px 20px;
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }

        .message-input input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 2px rgba(24, 119, 242, 0.1);
        }

        .send-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--accent-color);
            color: white;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .send-button:hover {
            transform: scale(1.05);
            background-color: #0056b3;
        }

        .chat-toolbar {
            display: flex;
            gap: 10px;
            padding: 10px;
            border-top: 1px solid var(--border-color);
            background-color: white;
        }

        .toolbar-button {
            background: none;
            border: none;
            padding: 8px;
            color: var(--text-muted);
            border-radius: 50%;
            transition: all 0.2s ease;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .toolbar-button:hover {
            background-color: var(--hover-bg);
            color: var(--accent-color);
        }

        .message-time {
            font-size: 0.8em;
            color: #bfc2c7;
            margin-top: 8px;
        }

        .typing-indicator {
            font-size: 0.8em;
            color: var(--text-muted);
            padding: 5px 15px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .typing-dot {
            width: 6px;
            height: 6px;
            background-color: var(--text-muted);
            border-radius: 50%;
            animation: typingAnimation 1.4s infinite;
            opacity: 0.5;
        }

        @keyframes typingAnimation {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        .attachment-preview {
            max-width: 200px;
            max-height: 200px;
            margin: 10px 0;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }

        .attachment-preview:hover {
            transform: scale(1.02);
        }

        .emoji-picker {
            position: absolute;
            bottom: 100%;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--notification-bg);
            color: white;
            border-radius: 50%;
            padding: 3px 6px;
            font-size: 12px;
            min-width: 20px;
            text-align: center;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .search-container {
            background-color: white;
        }

        .search-results {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: white;
            z-index: 1000;
        }

        .search-results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-result-item {
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .search-result-item:hover {
            background-color: var(--hover-bg);
        }

        .form-control, .form-select {
            background: #23283a;
            color: #F1F1F1;
            border: 1px solid #393e4a;
            border-radius: 12px;
            font-weight: 500;
        }
        .form-control:focus, .form-select:focus {
            background: #23283a;
            color: #fff;
            border-color: #C95792;
            box-shadow: 0 0 0 2px rgba(201, 87, 146, 0.15);
        }
        ::placeholder {
            color: #bfc2c7 !important;
            opacity: 1;
        }
        .h-100.d-flex.flex-column {
            display: flex !important;
            flex-direction: column !important;
            height: 100% !important;
        }
        .chat-messages {
            flex: 1 1 auto;
            min-height: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Urban Council Chat</a>
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <button class="btn btn-link text-white dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="/urban2/views/admin/dashboard.php">Dashboard</a></li>
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid chat-container">
        <div class="row h-100">
            <!-- Sidebar -->
            <div class="col-md-4 col-lg-3 p-0 chat-sidebar" id="chatSidebar">
                <div class="search-box">
                    <input type="text" class="form-control search-input" placeholder="Search..." id="searchInput">
                </div>
                <div class="chat-list">
                    <!-- System Messages Section -->
                    <div class="p-2">
                        <small class="text-muted">SYSTEM MESSAGES</small>
                    </div>
                    <div class="chat-list-item d-flex align-items-center position-relative" 
                         onclick="loadSystemMessages()">
                        <div class="user-avatar bg-warning text-white">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div>
                            <strong>System Announcements</strong>
                            <div class="department-label">All Users</div>
                        </div>
                        <span class="notification-badge" id="systemMessageBadge" style="display: none;">0</span>
                    </div>

                    <!-- Department Groups -->
                    <?php if (isset($groups['data']) && !empty($groups['data'])): ?>
                        <div class="p-2">
                            <small class="text-muted">DEPARTMENT GROUPS</small>
                        </div>
                        <?php foreach ($groups['data'] as $group): ?>
                            <div class="chat-list-item d-flex align-items-center position-relative" 
                                 onclick="loadGroupChat(<?php echo $group['id']; ?>, '<?php echo htmlspecialchars($group['name']); ?>')">
                                <div class="user-avatar bg-primary text-white">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($group['name']); ?></strong>
                                    <div class="department-label"><?php echo ucfirst($group['department']); ?> Department</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Admin Users -->
                    <?php if ($_SESSION['user_role'] === 'staff'): ?>
                        <div class="p-2">
                            <small class="text-muted">ADMINISTRATORS</small>
                        </div>
                        <?php 
                        $adminList = $adminController->getAllAdmins();
                        foreach ($adminList as $admin): ?>
                            <div class="chat-list-item d-flex align-items-center position-relative" 
                                onclick="loadIndividualChat(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?>')">
                                <div class="user-avatar bg-danger text-white">
                                    <i class="fas fa-user-shield"></i>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></strong>
                                    <div class="department-label">Administrator</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Staff Members -->
                    <div class="p-2">
                        <small class="text-muted">STAFF MEMBERS</small>
                    </div>
                    <?php foreach ($staffList as $staff): ?>
                        <?php if ($staff['id'] != $_SESSION['user_id']): ?>
                            <div class="chat-list-item d-flex align-items-center position-relative" 
                                 onclick="loadIndividualChat(<?php echo $staff['id']; ?>, '<?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>')">
                                <div class="user-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></strong>
                                    <div class="department-label"><?php echo ucfirst($staff['department']); ?> Department</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="search-container p-3 border-bottom">
                    <form id="searchForm" class="d-flex">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search messages...">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Chat Area -->
            <div class="col-md-8 col-lg-9 p-0 h-100 chat-area" id="chatArea">
                <div class="h-100 d-flex align-items-center justify-content-center text-muted">
                    <div class="text-center">
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <p>Select a chat to start messaging</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Template -->
    <template id="chatTemplate">
        <div class="h-100 d-flex flex-column">
            <div class="chat-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <button class="btn btn-link back-button me-2" onclick="showSidebar()">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="user-avatar position-relative" id="chatAvatar">
                        <i class="fas fa-user"></i>
                        <span class="online-status" id="onlineStatus"></span>
                    </div>
                    <div>
                        <strong id="chatTitle"></strong>
                        <div class="department-label" id="chatSubtitle"></div>
                    </div>
                </div>
                <div class="chat-header-actions">
                    <button class="btn btn-link" onclick="toggleSearch()">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-link" onclick="clearChat()">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            
            <div id="searchBar" class="p-2 border-bottom" style="display: none;">
                <input type="text" class="form-control" placeholder="Search in conversation..." onkeyup="searchMessages(this.value)">
            </div>

            <div class="typing-indicator" id="typingIndicator" style="display: none;">
                <span>Someone is typing</span>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
            <div class="chat-messages" id="messageContainer"></div>
            
            <div id="replyingTo" class="px-3 py-2 border-top" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">Replying to message</small>
                    <button class="btn btn-link btn-sm p-0" onclick="cancelReply()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="message-reply" id="replyPreview"></div>
            </div>

            <div class="message-input">
                <form id="messageForm" class="d-flex align-items-center gap-2" style="width: 100%;">
                    <button type="button" class="btn btn-link px-2" onclick="toggleEmojiPicker()" tabindex="-1" style="font-size: 1.3rem;">
                    <i class="far fa-smile"></i>
                </button>
                    <label class="btn btn-link px-2 mb-0" style="font-size: 1.3rem; cursor: pointer;">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" onchange="handleFileUpload(this)" accept="image/*,.pdf,.doc,.docx" style="display: none;">
                    </label>
                    <input type="text" class="form-control flex-grow-1" id="messageInput" 
                           placeholder="Type your message..." 
                           onkeypress="handleKeyPress(event)">
                    <button type="button" class="btn btn-primary send-button d-flex align-items-center justify-content-center" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </template>

    <!-- Add this after the chat-messages div -->
    <div id="searchResults" class="search-results" style="display: none;">
        <div class="search-results-header p-3 border-bottom">
            <h5>Search Results</h5>
            <button type="button" class="btn-close" id="closeSearchResults"></button>
        </div>
        <div class="search-results-body" style="max-height: 400px; overflow-y: auto;">
            <!-- Search results will be populated here -->
        </div>
    </div>

    <!-- Add jQuery library before other scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/emoji-mart@latest/dist/browser.js"></script>

    <script>
    let currentChatId = null;
    let currentChatType = null;
    let currentChatName = null;
    let messageCheckInterval = null;
    let typingTimeout = null;
    let replyingToMessage = null;

    // Search functionality
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const chatItems = document.querySelectorAll('.chat-list-item');
        
        chatItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    function loadIndividualChat(userId, userName) {
        currentChatId = userId;
        currentChatType = 'individual';
        currentChatName = userName;
        setupChat(userName, 'Staff Member');
        loadMessages();
        hideSidebar();
    }

    function loadGroupChat(groupId, groupName) {
        currentChatId = groupId;
        currentChatType = 'group';
        currentChatName = groupName;
        setupChat(groupName, 'Department Group');
        loadMessages();
        hideSidebar();
    }

    function loadSystemMessages() {
        currentChatType = 'system';
        currentChatId = 'system';
        currentChatName = 'System Announcements';
        setupChat('System Announcements', 'All Users');
        loadMessages();
        hideSidebar();
    }

    function setupChat(title, subtitle) {
        const chatArea = document.getElementById('chatArea');
        chatArea.innerHTML = document.getElementById('chatTemplate').innerHTML;

        document.getElementById('chatTitle').textContent = title;
        document.getElementById('chatSubtitle').textContent = subtitle;

        if (currentChatType === 'group') {
            document.getElementById('chatAvatar').innerHTML = '<i class="fas fa-users"></i>';
            document.getElementById('chatAvatar').classList.add('bg-primary', 'text-white');
        }

        document.getElementById('messageForm').onsubmit = function(e) {
            e.preventDefault();
            sendMessage();
        };

        // Clear previous interval and start new one
        if (messageCheckInterval) clearInterval(messageCheckInterval);
        messageCheckInterval = setInterval(loadMessages, 3000);
    }

    function loadMessages() {
        let url;
        if (currentChatType === 'system') {
            url = '../api/get_system_messages.php';
        } else if (currentChatType === 'individual') {
            url = '../api/get_messages.php?receiver_id=' + currentChatId;
        } else {
            url = '../api/get_group_messages.php?group_id=' + currentChatId;
        }

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    requestAnimationFrame(() => {
                        displayMessages(data.data);
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function displayMessages(messages) {
        const container = document.getElementById('messageContainer');
        const currentUserId = <?php echo $_SESSION['user_id']; ?>;
        
        // Create a temporary container to build the new content
        const tempContainer = document.createElement('div');
        
        messages.forEach(message => {
            const isSent = message.sender_id == currentUserId;
            const messageClass = isSent ? 'message-sent' : 'message-received';
            const time = new Date(message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            // Check if message already exists
            const existingMessage = document.getElementById(`message-${message.id}`);
            if (existingMessage) {
                // Update existing message if needed
                const existingContent = existingMessage.querySelector('.message-content');
                const existingTime = existingMessage.querySelector('.message-time');
                const existingStatus = existingMessage.querySelector('.message-status');
                
                if (existingContent.textContent !== message.message) {
                    existingContent.textContent = message.message;
                }
                if (existingTime.textContent !== time) {
                    existingTime.textContent = time;
                }
                if (existingStatus && isSent) {
                    existingStatus.innerHTML = `<i class="fas fa-check${message.is_read ? '-double' : ''} text-muted"></i>`;
                }
                return;
            }

            // Create new message element
            const messageElement = document.createElement('div');
            messageElement.className = `chat-message ${messageClass}`;
            messageElement.id = `message-${message.id}`;
            messageElement.style.opacity = '0';
            
            messageElement.innerHTML = `
                ${message.reply_to ? `
                    <div class="message-reply">
                        <small class="text-muted">Reply to: ${message.reply_to_content}</small>
                    </div>
                ` : ''}
                ${!isSent ? `<small class="text-muted">${message.sender_name}</small>` : ''}
                <div class="message-content">
                    ${message.message}
                    ${message.file_url ? `
                        <div class="attachment-preview">
                            ${message.file_type === 'image' 
                                ? `<img src="${message.file_url}" class="img-fluid">` 
                                : `<a href="${message.file_url}" target="_blank">
                                    <i class="fas fa-file"></i> ${message.file_name}
                                   </a>`
                            }
                        </div>
                    ` : ''}
                </div>
                <div class="message-time">${time}</div>
                ${isSent ? `
                    <div class="message-status">
                        <i class="fas fa-check${message.is_read ? '-double' : ''} text-muted"></i>
                    </div>
                ` : ''}
                <div class="message-actions">
                    <button class="action-button" onclick="replyToMessage(${message.id}, '${message.message}')">
                        <i class="fas fa-reply"></i>
                    </button>
                    ${isSent ? `
                        <button class="action-button" onclick="deleteMessage(${message.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            `;

            tempContainer.appendChild(messageElement);
        });

        // Remove messages that no longer exist
        const existingMessages = container.querySelectorAll('.chat-message');
        existingMessages.forEach(existingMessage => {
            const messageId = existingMessage.id.replace('message-', '');
            if (!messages.some(msg => msg.id == messageId)) {
                existingMessage.style.opacity = '0';
                setTimeout(() => existingMessage.remove(), 300);
            }
        });

        // Add new messages with fade-in animation
        const newMessages = Array.from(tempContainer.children);
        newMessages.forEach((message, index) => {
            container.appendChild(message);
            setTimeout(() => {
                message.style.transition = 'opacity 0.3s ease-in-out';
                message.style.opacity = '1';
            }, index * 50); // Stagger the fade-in
        });

        // Smooth scroll to bottom
        const scrollToBottom = () => {
            container.scrollTo({
                top: container.scrollHeight,
                behavior: 'smooth'
            });
        };

        // Only scroll if we're near the bottom
        const isNearBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 100;
        if (isNearBottom) {
            scrollToBottom();
        }
    }

    function sendMessage() {
        const input = document.getElementById('messageInput');
        const message = input.value.trim();

        if (!message) return;

        let url;
        let data = new FormData();
        data.append('message', message);
        data.append('sender_id', <?php echo $_SESSION['user_id']; ?>);

        if (currentChatType === 'system') {
            url = '../api/send_system_message.php';
        } else if (currentChatType === 'individual') {
            url = '../api/send_message.php';
            data.append('receiver_id', currentChatId);
            data.append('type', 'individual');
        } else {
            url = '../api/send_message.php';
            data.append('group_id', currentChatId);
            data.append('type', 'group');
        }

        // Show loading state
        const sendButton = document.querySelector('.send-button');
        const originalIcon = sendButton.innerHTML;
        sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        sendButton.disabled = true;

        fetch(url, {
            method: 'POST',
            body: data
        })
        .then(response => response.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error('Server response:', text);
                throw new Error('Invalid server response');
            }
            
            if (data.success) {
                input.value = '';
                cancelReply();
                loadMessages();
            } else {
                throw new Error(data.message || 'Failed to send message');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(error.message || 'An error occurred while sending the message');
        })
        .finally(() => {
            // Restore button state
            sendButton.innerHTML = originalIcon;
            sendButton.disabled = false;
        });
    }

    function handleKeyPress(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
        handleTyping();
    }

    function showSidebar() {
        document.getElementById('chatSidebar').classList.remove('hidden');
    }

    function hideSidebar() {
        if (window.innerWidth <= 768) {
            document.getElementById('chatSidebar').classList.add('hidden');
        }
    }

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            document.getElementById('chatSidebar').classList.remove('hidden');
        }
    });

    function handleTyping() {
        if (currentChatType === 'individual') {
            clearTimeout(typingTimeout);
            fetch('../api/update_typing_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: <?php echo $_SESSION['user_id']; ?>,
                    chat_id: currentChatId,
                    is_typing: true
                })
            });

            typingTimeout = setTimeout(() => {
                fetch('../api/update_typing_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        user_id: <?php echo $_SESSION['user_id']; ?>,
                        chat_id: currentChatId,
                        is_typing: false
                    })
                });
            }, 2000);
        }
    }

    function handleFileUpload(input) {
        const file = input.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('chat_type', currentChatType);
        formData.append('chat_id', currentChatId);

        fetch('../api/upload_file.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadMessages();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function toggleEmojiPicker() {
        const picker = document.querySelector('.emoji-picker');
        if (picker) {
            picker.remove();
        } else {
            const pickerDiv = document.createElement('div');
            pickerDiv.className = 'emoji-picker';
            document.querySelector('.message-input').appendChild(pickerDiv);

            new EmojiMart.Picker({
                onSelect: emoji => {
                    const input = document.getElementById('messageInput');
                    input.value += emoji.native;
                    pickerDiv.remove();
                }
            });
        }
    }

    function replyToMessage(messageId, content) {
        replyingToMessage = messageId;
        document.getElementById('replyingTo').style.display = 'block';
        document.getElementById('replyPreview').textContent = content;
    }

    function cancelReply() {
        replyingToMessage = null;
        document.getElementById('replyingTo').style.display = 'none';
    }

    function toggleSearch() {
        const searchBar = document.getElementById('searchBar');
        searchBar.style.display = searchBar.style.display === 'none' ? 'block' : 'none';
    }

    function searchMessages() {
        const searchInput = document.getElementById('searchInput');
        const query = searchInput.value.trim();
        
        if (query.length < 2) {
            return;
        }

        const url = new URL('../api/search_messages.php', window.location.origin);
        url.searchParams.append('chat_type', currentChatType);
        url.searchParams.append('chat_id', currentChatId);
        url.searchParams.append('query', query);

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Search request failed');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    displaySearchResults(data.data);
                } else {
                    console.error('Search failed:', data.message);
                }
            })
            .catch(error => {
                console.error('Error searching messages:', error);
            });
    }

    function clearChat() {
        if (confirm('Are you sure you want to clear this chat?')) {
            fetch('../api/clear_chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    chat_type: currentChatType,
                    chat_id: currentChatId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    loadMessages();
                } else {
                    console.error('Failed to clear chat:', data.message);
                }
            })
            .catch(error => {
                console.error('Error clearing chat:', error);
            });
        }
    }

    // Update the notification sound handling
    let audioContext = null;
    let notificationSound = null;
    let isAudioInitialized = false;

    function initializeAudio() {
        if (isAudioInitialized) return;
        
        try {
            // Try to load the notification sound
            notificationSound = new Audio('../assets/notification.mp3');
            notificationSound.onerror = function() {
                console.log('Notification sound not found, using default sound');
                initializeWebAudio();
            };
            notificationSound.oncanplaythrough = function() {
                console.log('Notification sound loaded successfully');
            };
            isAudioInitialized = true;
        } catch (e) {
            console.log('Error initializing audio:', e);
            initializeWebAudio();
        }
    }

    function initializeWebAudio() {
        try {
            if (!audioContext) {
                audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
        } catch (e) {
            console.log('Error initializing Web Audio API:', e);
        }
    }

    function playNotificationSound() {
        if (!isAudioInitialized) {
            initializeAudio();
        }

        try {
            if (notificationSound && !notificationSound.error) {
                notificationSound.play().catch(e => {
                    console.log('Error playing notification sound:', e);
                    playDefaultSound();
                });
            } else {
                playDefaultSound();
            }
        } catch (e) {
            console.log('Error playing sound:', e);
            playDefaultSound();
        }
    }

    function playDefaultSound() {
        if (!audioContext) {
            initializeWebAudio();
        }

        if (audioContext) {
            try {
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();
                
                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);
                
                oscillator.type = 'sine';
                oscillator.frequency.value = 800;
                gainNode.gain.value = 0.1;
                
                oscillator.start();
                setTimeout(() => {
                    oscillator.stop();
                }, 100);
            } catch (e) {
                console.log('Error playing default sound:', e);
            }
        }
    }

    // Initialize audio on user interaction
    document.addEventListener('click', function() {
        if (!isAudioInitialized) {
            initializeAudio();
        }
    }, { once: true });

    // Add deleteMessage function
    function deleteMessage(messageId) {
        if (confirm('Are you sure you want to delete this message?')) {
            fetch('../api/delete_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    message_id: messageId
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    loadMessages();
                } else {
                    console.error('Failed to delete message:', data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting message:', error);
            });
        }
    }

    $(document).ready(function() {
        // Handle search form submission
        $('#searchForm').on('submit', function(e) {
            e.preventDefault();
            const searchTerm = $('#searchInput').val().trim();
            if (searchTerm) {
                searchMessages(searchTerm);
            }
        });

        // Close search results
        $('#closeSearchResults').on('click', function() {
            $('#searchResults').hide();
            $('.chat-messages').show();
        });

        // Function to search messages
        function searchMessages(searchTerm) {
            $.ajax({
                url: '../api/chat/search.php',
                method: 'POST',
                data: {
                    search_term: searchTerm,
                    type: currentChatType,
                    group_id: currentChatId
                },
                success: function(response) {
                    if (response.success) {
                        displaySearchResults(response.data);
                    } else {
                        showNotification('Error searching messages', 'error');
                    }
                },
                error: function() {
                    showNotification('Error searching messages', 'error');
                }
            });
        }

        // Function to display search results
        function displaySearchResults(results) {
            const resultsContainer = $('.search-results-body');
            resultsContainer.empty();
            
            if (results.length === 0) {
                resultsContainer.html('<div class="p-3 text-center">No results found</div>');
            } else {
                results.forEach(function(message) {
                    const messageHtml = `
                        <div class="search-result-item p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${message.sender_name}</strong>
                                    <small class="text-muted">${message.chat_name ? 'in ' + message.chat_name : ''}</small>
                                </div>
                                <small class="text-muted">${formatDate(message.created_at)}</small>
                            </div>
                            <div class="mt-2">${message.message}</div>
                        </div>
                    `;
                    resultsContainer.append(messageHtml);
                });
            }

            $('.chat-messages').hide();
            $('#searchResults').show();
        }
    });
    </script>
</body>
</html> 
