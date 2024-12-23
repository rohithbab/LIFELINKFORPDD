let notificationSound;

document.addEventListener('DOMContentLoaded', function() {
    notificationSound = new Audio('../assets/sounds/notification.mp3');
    notificationSound.volume = 0.5; // Set volume to 50%
    
    // Initialize notifications
    updateNotifications();
    
    // Check for new notifications every 30 seconds
    setInterval(updateNotifications, 30000);
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const bell = document.querySelector('.notification-bell-container');
        const dropdown = document.querySelector('.notification-dropdown');
        
        if (!bell.contains(event.target) && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    });
});

function toggleNotifications() {
    const dropdown = document.querySelector('.notification-dropdown');
    dropdown.classList.toggle('show');
}

function updateNotifications() {
    fetch('../backend/php/get_notifications.php?action=get_unread')
        .then(response => response.json())
        .then(data => {
            updateNotificationUI(data.notifications, data.unread_count);
        })
        .catch(error => console.error('Error fetching notifications:', error));
}

function updateNotificationUI(notifications, unreadCount) {
    const container = document.querySelector('.notification-dropdown');
    const countBadge = document.querySelector('.notification-count');
    
    // Update count badge
    if (unreadCount > 0) {
        countBadge.style.display = 'block';
        countBadge.textContent = unreadCount;
    } else {
        countBadge.style.display = 'none';
    }
    
    // Update notification list
    const notificationsList = container.querySelector('.notification-list');
    notificationsList.innerHTML = '';
    
    if (notifications.length === 0) {
        notificationsList.innerHTML = `
            <div class="notification-empty">
                No new notifications
            </div>`;
        return;
    }
    
    notifications.forEach(notification => {
        const item = document.createElement('div');
        item.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
        item.onclick = () => handleNotificationClick(notification);
        
        item.innerHTML = `
            <div class="notification-content">
                ${notification.formatted_message}
                <div class="time">${notification.formatted_time}</div>
            </div>`;
            
        notificationsList.appendChild(item);
    });
}

function handleNotificationClick(notification) {
    // Mark as read
    fetch('../backend/php/get_notifications.php?action=mark_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `notification_id=${notification.notification_id}`
    })
    .then(() => {
        // Navigate to the link if provided
        if (notification.link_url) {
            window.location.href = notification.link_url;
        }
        // Update notifications UI
        updateNotifications();
    })
    .catch(error => console.error('Error marking notification as read:', error));
}

function playNotificationSound() {
    notificationSound.play().catch(error => {
        console.log('Error playing notification sound:', error);
    });
}
