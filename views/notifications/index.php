<?php
// TransitOps Notifications View
if (!defined('SECURE_ACCESS')) exit;
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h3 class="fw-bold mb-0">System Alerts</h3>
        <p class="text-secondary small mb-0">Automated compliance warnings: license expirations, insurance renewals, and maintenance deadlines</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <button class="btn btn-outline-secondary btn-sm" onclick="markAllRead()">
            <i class="fas fa-check-double me-2"></i> Mark All Read
        </button>
    </div>
</div>

<div class="row g-3">
    <?php if (empty($notifications)): ?>
    <div class="col-12">
        <div class="premium-card text-center py-5">
            <i class="fas fa-bell-slash text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
            <h5 class="mt-3 text-muted fw-medium">All Clear!</h5>
            <p class="text-secondary small">No active alerts. All vehicles and drivers are compliant.</p>
        </div>
    </div>
    <?php else: ?>
    <?php foreach ($notifications as $n): ?>
    <?php
        $iconClass = 'fa-info-circle text-info';
        $cardBorder = 'border-info';
        if ($n['type'] === 'danger')  { $iconClass = 'fa-exclamation-triangle text-danger'; $cardBorder = 'border-danger'; }
        if ($n['type'] === 'warning') { $iconClass = 'fa-exclamation-circle text-warning'; $cardBorder = 'border-warning'; }
        if ($n['type'] === 'success') { $iconClass = 'fa-check-circle text-success'; $cardBorder = 'border-success'; }
        $isRead = $n['is_read'];
    ?>
    <div class="col-12 col-md-6">
        <div class="premium-card d-flex gap-3 align-items-start <?php echo !$isRead ? 'border-start border-3 ' . $cardBorder : 'opacity-75'; ?>">
            <div class="mt-1 fs-4 flex-shrink-0">
                <i class="fas <?php echo $iconClass; ?>"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <h6 class="fw-bold mb-0 <?php echo $isRead ? 'text-secondary' : ''; ?>">
                        <?php echo htmlspecialchars($n['title']); ?>
                    </h6>
                    <?php if (!$isRead): ?>
                    <span class="badge bg-primary ms-2 flex-shrink-0">New</span>
                    <?php endif; ?>
                </div>
                <p class="text-secondary small mb-2"><?php echo htmlspecialchars($n['message']); ?></p>
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-light small" style="font-size: 0.75rem;">
                        <i class="far fa-clock me-1"></i><?php echo date('M j, Y H:i', strtotime($n['created_at'])); ?>
                    </span>
                    <div class="d-flex gap-2">
                        <?php if (!$isRead): ?>
                        <button class="btn btn-link btn-sm text-primary p-0 small" onclick="markRead(<?php echo $n['id']; ?>, this)">
                            Mark Read
                        </button>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-primary rounded-circle" onclick="editNotification(<?php echo $n['id']; ?>)" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-danger rounded-circle" onclick="deleteNotification(<?php echo $n['id']; ?>)" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function markRead(id, btn) {
    fetch(BASE_URL + '/notifications/read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const card = btn.closest('.premium-card');
            card.classList.add('opacity-75');
            card.classList.remove('border-start','border-3','border-danger','border-warning','border-info','border-success');
            btn.closest('.d-flex').querySelector('.badge')?.remove();
            btn.remove();
        }
    });
}

function markAllRead() {
    Swal.fire({
        title: 'Mark All Read?',
        text: 'This clears all unread notification badges.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        confirmButtonText: 'Yes, Mark All'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + '/notifications/read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({})
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    AjaxOps.toast('All notifications marked as read.');
                    setTimeout(() => window.location.reload(), 1000);
                }
            });
        }
    });
}

function editNotification(id) {
    const card = document.querySelector(`[onclick="editNotification(${id})"]`).closest('.premium-card');
    const title = card.querySelector('h6').innerText;
    const message = card.querySelector('p').innerText;
    const typeSelect = card.querySelector('.border-danger') ? 'danger' : (card.querySelector('.border-warning') ? 'warning' : 'info');

    Swal.fire({
        title: 'Edit Notification',
        html: `
            <input id="notif-title" class="swal2-input" placeholder="Title" value="${title}">
            <textarea id="notif-message" class="swal2-input" placeholder="Message" rows="3">${message}</textarea>
            <select id="notif-type" class="swal2-input">
                <option value="info" ${typeSelect === 'info' ? 'selected' : ''}>Info</option>
                <option value="warning" ${typeSelect === 'warning' ? 'selected' : ''}>Warning</option>
                <option value="danger" ${typeSelect === 'danger' ? 'selected' : ''}>Danger</option>
                <option value="success" ${typeSelect === 'success' ? 'selected' : ''}>Success</option>
            </select>
        `,
        showCancelButton: true,
        confirmButtonText: 'Update',
        confirmButtonColor: '#4f46e5',
        preConfirm: () => {
            return {
                id: id,
                title: document.getElementById('notif-title').value,
                message: document.getElementById('notif-message').value,
                type: document.getElementById('notif-type').value
            };
        }
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + '/notifications/update', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify(result.value)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    AjaxOps.toast('Notification updated successfully.');
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    Swal.fire('Error', data.error || 'Failed to update notification', 'error');
                }
            });
        }
    });
}

function deleteNotification(id) {
    Swal.fire({
        title: 'Delete Notification?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Delete'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(BASE_URL + '/notifications/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ id: id })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[onclick="deleteNotification(${id})"]`).closest('.col-12');
                    card.style.transition = 'all 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'translateX(-100%)';
                    setTimeout(() => card.remove(), 300);
                    AjaxOps.toast('Notification deleted successfully.');
                } else {
                    Swal.fire('Error', data.error || 'Failed to delete notification', 'error');
                }
            });
        }
    });
}
</script>
