<?php
// TransitOps Maintenance Module View
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';
$isManagerOrSafety = AuthHelper::hasPermission('maintenance_manage');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">Maintenance Log</h3>
        <p class="text-secondary small mb-0">Track schedules, repairs, routine services, vendors, and cost centers</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if ($isManagerOrSafety): ?>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus-square me-2"></i> Log Service Ticket
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Table Card -->
<div class="premium-card">
    <div class="table-responsive">
        <table id="maintenanceTable" class="table table-hover w-100 align-middle">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Service Type</th>
                    <th>Description</th>
                    <th>Service Date</th>
                    <th>Cost</th>
                    <th>Vendor</th>
                    <th>Next Service Target</th>
                    <th>Status</th>
                    <th style="width: 120px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $m): ?>
                <tr>
                    <td>
                        <div class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($m['registration_number']); ?></div>
                        <div class="text-secondary small"><?php echo htmlspecialchars($m['vehicle_name']); ?></div>
                    </td>
                    <td>
                        <span class="badge bg-secondary-subtle text-secondary-emphasis fw-medium"><?php echo htmlspecialchars($m['type']); ?></span>
                    </td>
                    <td>
                        <div class="text-wrap small text-truncate-custom" style="max-width: 250px;" title="<?php echo htmlspecialchars($m['description']); ?>">
                            <?php echo htmlspecialchars($m['description']); ?>
                        </div>
                    </td>
                    <td>
                        <div class="small fw-semibold"><?php echo date('Y-m-d', strtotime($m['date'])); ?></div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?php echo CURRENCY_SYMBOL . number_format($m['cost'], 2); ?></div>
                    </td>
                    <td>
                        <div class="small"><i class="fas fa-building text-muted me-1"></i> <?php echo htmlspecialchars($m['vendor']); ?></div>
                    </td>
                    <td>
                        <div class="small fw-medium"><i class="fas fa-tachometer-alt text-muted me-1"></i> <?php echo number_format($m['next_service_km']); ?> km</div>
                        <?php 
                        $daysToNext = (strtotime($m['next_service_date']) - time()) / 86400;
                        ?>
                        <div class="small text-muted mt-0.5">
                            <i class="far fa-calendar-alt me-1"></i> 
                            <?php if ($daysToNext < 0 && $m['status'] !== 'Completed'): ?>
                                <span class="text-danger fw-semibold">Overdue</span>
                            <?php elseif ($daysToNext <= 7 && $m['status'] !== 'Completed'): ?>
                                <span class="text-warning fw-semibold">Due in <?php echo ceil($daysToNext); ?>d</span>
                            <?php else: ?>
                                <span><?php echo date('Y-m-d', strtotime($m['next_service_date'])); ?></span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'bg-secondary';
                        if ($m['status'] === 'Pending') $badgeClass = 'bg-secondary text-light';
                        elseif ($m['status'] === 'In Progress') $badgeClass = 'bg-warning text-dark';
                        elseif ($m['status'] === 'Completed') $badgeClass = 'bg-success';
                        ?>
                        <span class="badge badge-premium <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($m['status']); ?></span>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <?php if ($isManagerOrSafety): ?>
                                <button class="btn btn-outline-primary btn-sm rounded-circle" onclick="openEditModal(<?php echo $m['id']; ?>)" title="Edit Ticket">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <?php if ($m['status'] === 'In Progress'): ?>
                                    <button class="btn btn-success btn-sm rounded-circle" onclick="completeTicket(<?php echo $m['id']; ?>)" title="Complete & Release Vehicle">
                                        <i class="fas fa-check"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-outline-danger btn-sm rounded-circle" onclick="deleteTicket(<?php echo $m['id']; ?>)" title="Delete Service Log">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-muted small">Read Only</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Maintenance Save Form Modal -->
<?php if ($isManagerOrSafety): ?>
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-labelledby="maintenanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="maintenanceModalLabel">Log Service Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="maintenanceForm" action="<?php echo BASE_URL; ?>/maintenance/create" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="maintenance_id" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="vehicle_id" class="form-label required-field">Vehicle</label>
                            <select class="form-select font-monospace" id="vehicle_id" name="vehicle_id" required>
                                <option value="">-- Select Fleet Vehicle --</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>">
                                        <?php echo htmlspecialchars($v['registration_number'] . ' - ' . $v['name'] . ' (Odo: ' . number_format($v['odometer']) . ' km)'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="type" class="form-label required-field">Maintenance Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="Routine">Routine Service (Oil change, Tuning)</option>
                                <option value="Repair">Repairs (Brakes, Engine components)</option>
                                <option value="Inspection">Inspection (State/Commercial checks)</option>
                                <option value="Accident">Accident / Bodywork repairs</option>
                                <option value="Other">Other Maintenance</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label for="description" class="form-label required-field">Work Description Details</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Explain the specific issues diagnosed, work performed, and parts replaced." required></textarea>
                        </div>

                        <div class="col-md-4">
                            <label for="date" class="form-label required-field">Service Start Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cost" class="form-label required-field">Service Cost ($)</label>
                            <input type="number" step="0.01" class="form-control" id="cost" name="cost" min="0" placeholder="350.00" required>
                        </div>
                        <div class="col-md-4">
                            <label for="vendor" class="form-label required-field">Service Shop / Vendor</label>
                            <input type="text" class="form-control" id="vendor" name="vendor" placeholder="Apex Auto Shop" required>
                        </div>

                        <div class="col-md-6">
                            <label for="next_service_km" class="form-label required-field">Next Service Odometer (km Target)</label>
                            <input type="number" class="form-control" id="next_service_km" name="next_service_km" placeholder="55000" required>
                        </div>
                        <div class="col-md-6">
                            <label for="next_service_date" class="form-label required-field">Next Service Date Target</label>
                            <input type="date" class="form-control" id="next_service_date" name="next_service_date" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="status" class="form-label">Service Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Pending">Scheduled (Pending)</option>
                                <option value="In Progress">Active (In Shop / Work In Progress)</option>
                                <option value="Completed">Completed (Returned to available fleet)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    AjaxOps.initTable('#maintenanceTable');
    
    <?php if ($isManagerOrSafety): ?>
        AjaxOps.submitForm(document.getElementById('maintenanceForm'), function() {
            $('#maintenanceModal').modal('hide');
            setTimeout(() => window.location.reload(), 1000);
        });
    <?php endif; ?>
});

<?php if ($isManagerOrSafety): ?>
const maintenanceModal = new bootstrap.Modal(document.getElementById('maintenanceModal'));
const maintenanceForm = document.getElementById('maintenanceForm');

function openAddModal() {
    maintenanceForm.reset();
    document.getElementById('maintenance_id').value = '';
    document.getElementById('maintenanceModalLabel').innerText = 'Log Fleet Service Ticket';
    maintenanceForm.setAttribute('action', BASE_URL + '/maintenance/create');
    maintenanceModal.show();
}

function openEditModal(id) {
    maintenanceForm.reset();
    document.getElementById('maintenanceModalLabel').innerText = 'Modify Service Ticket Details';
    maintenanceForm.setAttribute('action', BASE_URL + '/maintenance/update');

    AjaxOps.editLoad(BASE_URL + '/maintenance/get?id=' + id, function(data) {
        document.getElementById('maintenance_id').value = data.id;
        document.getElementById('vehicle_id').value = data.vehicle_id;
        document.getElementById('type').value = data.type;
        document.getElementById('description').value = data.description;
        document.getElementById('date').value = data.date;
        document.getElementById('cost').value = data.cost;
        document.getElementById('vendor').value = data.vendor;
        document.getElementById('next_service_km').value = data.next_service_km;
        document.getElementById('next_service_date').value = data.next_service_date;
        document.getElementById('status').value = data.status;
        
        maintenanceModal.show();
    });
}

function completeTicket(id) {
    Swal.fire({
        title: 'Complete Maintenance Work?',
        text: "This resolves the ticket and restores the vehicle back to 'Available' status.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, Complete Ticket'
    }).then((result) => {
        if (result.isConfirmed) {
            AjaxOps.showSpinner();
            fetch(BASE_URL + '/maintenance/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                AjaxOps.hideSpinner();
                if (data.success) {
                    AjaxOps.toast(data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    Swal.fire('Failed', data.error || 'Operation failed.', 'error');
                }
            });
        }
    });
}

function deleteTicket(id) {
    AjaxOps.deleteConfirm(BASE_URL + '/maintenance/delete', id, function() {
        setTimeout(() => window.location.reload(), 1000);
    });
}
<?php endif; ?>
</script>
