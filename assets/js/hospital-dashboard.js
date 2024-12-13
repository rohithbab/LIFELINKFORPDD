// Hospital Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initializeDashboard();
    
    // Load initial data
    loadDashboardData();
    
    // Set up event listeners
    setupEventListeners();
});

// Initialize dashboard components
function initializeDashboard() {
    // Initialize notifications
    initializeNotifications();
    
    // Initialize sidebar navigation
    initializeSidebar();
}

// Load dashboard data
function loadDashboardData() {
    // Load metrics
    fetchMetrics();
    
    // Load recent activities
    fetchRecentActivities();
    
    // Load urgent cases
    fetchUrgentCases();
}

// Fetch dashboard metrics
function fetchMetrics() {
    fetch('../../backend/php/fetch_hospital_metrics.php')
        .then(response => response.json())
        .then(data => {
            updateMetrics(data);
        })
        .catch(error => {
            console.error('Error fetching metrics:', error);
        });
}

// Update metrics display
function updateMetrics(data) {
    document.getElementById('totalDonors').textContent = data.totalDonors || 0;
    document.getElementById('totalRecipients').textContent = data.totalRecipients || 0;
    document.getElementById('pendingRequests').textContent = data.pendingRequests || 0;
    document.getElementById('approvedDonations').textContent = data.approvedDonations || 0;
}

// Fetch recent activities
function fetchRecentActivities() {
    fetch('../../backend/php/fetch_recent_activities.php')
        .then(response => response.json())
        .then(data => {
            updateRecentActivities(data);
        })
        .catch(error => {
            console.error('Error fetching activities:', error);
        });
}

// Update recent activities display
function updateRecentActivities(activities) {
    const container = document.getElementById('recentActivities');
    container.innerHTML = '';

    activities.forEach(activity => {
        const activityElement = createActivityElement(activity);
        container.appendChild(activityElement);
    });
}

// Create activity element
function createActivityElement(activity) {
    const div = document.createElement('div');
    div.className = 'activity-item';
    div.innerHTML = `
        <span class="activity-type">${activity.type}</span>
        <p>${activity.description}</p>
        <small>${formatDate(activity.timestamp)}</small>
    `;
    return div;
}

// Fetch urgent cases
function fetchUrgentCases() {
    fetch('../../backend/php/fetch_urgent_cases.php')
        .then(response => response.json())
        .then(data => {
            updateUrgentCases(data);
        })
        .catch(error => {
            console.error('Error fetching urgent cases:', error);
        });
}

// Update urgent cases display
function updateUrgentCases(cases) {
    const container = document.getElementById('urgentCases');
    container.innerHTML = '';

    cases.forEach(case_ => {
        const caseElement = createUrgentCaseElement(case_);
        container.appendChild(caseElement);
    });
}

// Create urgent case element
function createUrgentCaseElement(case_) {
    const div = document.createElement('div');
    div.className = 'urgent-item';
    div.innerHTML = `
        <h4>${case_.recipientName}</h4>
        <p>Organ Needed: ${case_.organType}</p>
        <p>Blood Type: ${case_.bloodType}</p>
        <p>Urgency Level: ${case_.urgencyLevel}</p>
        <button class="btn btn-primary" onclick="handleUrgentCase(${case_.id})">
            Take Action
        </button>
    `;
    return div;
}

// Initialize notifications
function initializeNotifications() {
    const notificationsBtn = document.querySelector('.notifications-btn');
    const notificationsContent = document.querySelector('.notifications-content');
    
    notificationsBtn.addEventListener('click', () => {
        notificationsContent.classList.toggle('active');
        fetchNotifications();
    });

    // Close notifications when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.notifications-dropdown')) {
            notificationsContent.classList.remove('active');
        }
    });
}

// Initialize sidebar
function initializeSidebar() {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    const navLinks = document.querySelectorAll('.sidebar-nav a');

    // Toggle sidebar
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    // Handle navigation
    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Remove active class from all links
            navLinks.forEach(l => l.parentElement.classList.remove('active'));
            
            // Add active class to clicked link
            link.parentElement.classList.add('active');
            
            // Show corresponding section
            const sectionId = link.dataset.section;
            showSection(sectionId);
            
            // Close sidebar on mobile
            if (window.innerWidth <= 1024) {
                sidebar.classList.remove('active');
            }
        });
    });
}

// Show content section
function showSection(sectionId) {
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => {
        section.classList.remove('active');
    });
    
    document.getElementById(sectionId).classList.add('active');
    
    // Load section specific data
    switch(sectionId) {
        case 'donors':
            loadDonorsSection();
            break;
        case 'recipients':
            loadRecipientsSection();
            break;
        case 'analytics':
            loadAnalyticsSection();
            break;
        case 'notifications':
            loadNotificationsSection();
            break;
        case 'settings':
            loadSettingsSection();
            break;
    }
}

// Setup event listeners
function setupEventListeners() {
    // User dropdown
    const userBtn = document.querySelector('.user-btn');
    const userDropdown = document.querySelector('.user-dropdown-content');
    
    userBtn.addEventListener('click', () => {
        userDropdown.classList.toggle('active');
    });

    // Close user dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.user-dropdown')) {
            userDropdown.classList.remove('active');
        }
    });

    // Mark all notifications as read
    const markAllReadBtn = document.querySelector('.mark-all-read');
    markAllReadBtn.addEventListener('click', markAllNotificationsAsRead);
}

// Utility function to format date
function formatDate(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Handle urgent case action
function handleUrgentCase(caseId) {
    // Implement urgent case handling logic
    console.log('Handling urgent case:', caseId);
}

// Mark all notifications as read
function markAllNotificationsAsRead() {
    fetch('../../backend/php/mark_notifications_read.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('#notificationCount').textContent = '0';
            // Refresh notifications list
            fetchNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking notifications as read:', error);
    });
}

// Auto-refresh dashboard data every 5 minutes
setInterval(loadDashboardData, 300000);
