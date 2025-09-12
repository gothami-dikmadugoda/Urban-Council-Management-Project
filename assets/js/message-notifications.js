// Function to update message count
function updateMessageCount() {
    fetch('/urban2/api/get_unread_message_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const messageCount = document.getElementById('message-count');
                if (data.count > 0) {
                    messageCount.textContent = data.count;
                    messageCount.style.display = 'block';
                    document.getElementById('message-icon').classList.add('vibrate');
                } else {
                    messageCount.style.display = 'none';
                    document.getElementById('message-icon').classList.remove('vibrate');
                }
            }
        })
        .catch(error => {
            console.error('Error fetching message count:', error);
        });
}

// Update message count every 30 seconds
setInterval(updateMessageCount, 30000);

// Initial check when page loads
document.addEventListener('DOMContentLoaded', updateMessageCount);

// Function to mark message as read
function markMessageAsRead(messageId) {
    fetch('/urban2/api/mark_message_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ message_id: messageId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateMessageCount();
        }
    })
    .catch(error => {
        console.error('Error marking message as read:', error);
    });
} 