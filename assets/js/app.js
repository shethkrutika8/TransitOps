// TransitOps App-Wide JS Logic

document.addEventListener('DOMContentLoaded', function() {
    // 1. Theme Management (Light / Dark Mode)
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeIcon = themeToggleBtn ? themeToggleBtn.querySelector('i') : null;
    
    // Check saved theme or system preference
    const savedTheme = localStorage.getItem('transitops-theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('transitops-theme', newTheme);
            updateThemeIcon(newTheme);
            
            // Dispatch a custom event so charts can redraw themselves with new colors
            window.dispatchEvent(new CustomEvent('themeChanged', { detail: newTheme }));
        });
    }

    function updateThemeIcon(theme) {
        if (!themeIcon) return;
        if (theme === 'dark') {
            themeIcon.className = 'fas fa-sun';
            themeToggleBtn.setAttribute('title', 'Switch to Light Mode');
        } else {
            themeIcon.className = 'fas fa-moon';
            themeToggleBtn.setAttribute('title', 'Switch to Dark Mode');
        }
    }

    // 2. Responsive Sidebar Toggle
    const sidebarToggleBtn = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggleBtn && sidebar) {
        sidebarToggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 992 && sidebar.classList.contains('show')) {
                if (!sidebar.contains(e.target) && e.target !== sidebarToggleBtn) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }

    // 3. Dynamic Notifications Polling
    initNotifications();
    // Poll every 60 seconds
    setInterval(initNotifications, 60000);
});

// Fetch latest unread notifications via AJAX
function initNotifications() {
    const badge = document.getElementById('notification-badge');
    const dropdownList = document.getElementById('notification-list');
    
    if (!badge || !dropdownList) return;

    fetch(BASE_URL + '/notifications/latest', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.status === 401) {
            // Silence auth errors during polling
            return null;
        }
        return response.json();
    })
    .then(data => {
        if (!data || !data.success) return;
        
        const count = data.unread_count;
        if (count > 0) {
            badge.classList.remove('d-none');
            badge.innerText = count > 9 ? '9+' : count;
        } else {
            badge.classList.add('d-none');
        }

        // Populate dropdown
        if (data.notifications.length === 0) {
            dropdownList.innerHTML = '<li class="p-3 text-center text-muted">No new alerts</li>';
        } else {
            let html = '';
            data.notifications.forEach(n => {
                let iconClass = 'fa-info-circle text-info';
                if (n.type === 'danger') iconClass = 'fa-exclamation-triangle text-danger';
                if (n.type === 'warning') iconClass = 'fa-exclamation-circle text-warning';
                if (n.type === 'success') iconClass = 'fa-check-circle text-success';
                
                html += `
                    <li>
                        <a class="dropdown-item p-3 border-bottom d-flex align-items-start gap-3" href="#" onclick="markNotificationRead(${n.id}, '${n.related_type}')">
                            <i class="fas ${iconClass} mt-1 fs-5"></i>
                            <div>
                                <h6 class="mb-1 text-wrap fw-semibold" style="font-size: 0.85rem;">${n.title}</h6>
                                <p class="mb-1 text-wrap text-muted small" style="font-size: 0.8rem;">${n.message}</p>
                                <span class="text-light small" style="font-size: 0.7rem;">${formatTimeAgo(n.created_at)}</span>
                            </div>
                        </a>
                    </li>
                `;
            });
            html += `<li><a class="dropdown-item text-center text-primary py-2 font-medium small" href="${BASE_URL}/notifications">View All Alerts</a></li>`;
            dropdownList.innerHTML = html;
        }
    })
    .catch(err => console.log('Notification fetch failed:', err));
}

// Mark notification as read
function markNotificationRead(id, entity) {
    const url = BASE_URL + '/notifications/read';
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            initNotifications();
            // Redirect based on entity
            if (entity === 'Vehicle') {
                window.location.href = BASE_URL + '/vehicles';
            } else if (entity === 'Driver') {
                window.location.href = BASE_URL + '/drivers';
            } else if (entity === 'Maintenance') {
                window.location.href = BASE_URL + '/maintenance';
            }
        }
    });
}

function formatTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return `${minutes}m ago`;
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
}
