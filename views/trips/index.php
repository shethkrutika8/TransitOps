<?php
// TransitOps Trips Module View
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';
$isDispatcherOrAdmin = AuthHelper::hasPermission('trips_manage');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">Trip Operations</h3>
        <p class="text-secondary small mb-0">Dispatch management, routing, cargo assignments, and lifecycle tracking</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if ($isDispatcherOrAdmin): ?>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-route me-2"></i> Create Trip
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Banner -->
<div class="row g-3 mb-4">
    <?php
    $counts = ['Draft' => 0, 'Dispatched' => 0, 'Completed' => 0, 'Cancelled' => 0];
    $total = count($trips);
    foreach ($trips as $t) {
        if (isset($counts[$t['status']])) $counts[$t['status']]++;
    }
    ?>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-info py-3">
            <div class="kpi-title text-info">Draft Planning</div>
            <div class="kpi-value text-info"><?php echo $counts['Draft']; ?></div>
            <i class="fas fa-clipboard-list kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-primary py-3">
            <div class="kpi-title text-primary">Dispatched (Active)</div>
            <div class="kpi-value text-primary"><?php echo $counts['Dispatched']; ?></div>
            <i class="fas fa-shipping-fast kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-success py-3">
            <div class="kpi-title text-success">Completed Today</div>
            <div class="kpi-value text-success"><?php echo $counts['Completed']; ?></div>
            <i class="fas fa-check-double kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-danger py-3">
            <div class="kpi-title text-danger">Cancelled</div>
            <div class="kpi-value text-danger"><?php echo $counts['Cancelled']; ?></div>
            <i class="fas fa-times-circle kpi-icon"></i>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="premium-card">
    <div class="table-responsive">
        <table id="tripsTable" class="table table-hover w-100 align-middle">
            <thead>
                <tr>
                    <th>Trip ID</th>
                    <th>Route Details</th>
                    <th>Vehicle</th>
                    <th>Driver Assigned</th>
                    <th>Cargo Info</th>
                    <th>Time Frame</th>
                    <th>Revenue</th>
                    <th>Status</th>
                    <th style="width: 130px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($trips as $t): ?>
                <tr>
                    <td>
                        <span class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($t['trip_number']); ?></span>
                    </td>
                    <td>
                        <div class="fw-semibold text-wrap" style="max-width: 200px;">
                            <?php echo htmlspecialchars($t['source']); ?> <i class="fas fa-long-arrow-alt-right mx-1 text-muted"></i> <?php echo htmlspecialchars($t['destination']); ?>
                        </div>
                        <div class="text-secondary small"><?php echo number_format($t['planned_distance']); ?> km planned</div>
                    </td>
                    <td>
                        <div class="fw-medium"><?php echo htmlspecialchars($t['vehicle_name']); ?></div>
                        <div class="text-secondary font-monospace small"><?php echo htmlspecialchars($t['registration_number']); ?></div>
                    </td>
                    <td>
                        <div class="fw-medium"><?php echo htmlspecialchars($t['driver_name']); ?></div>
                        <div class="text-muted small">ID: <?php echo htmlspecialchars($t['employee_id']); ?></div>
                    </td>
                    <td>
                        <div class="fw-semibold text-truncate small" style="max-width: 120px;"><?php echo htmlspecialchars($t['cargo_type']); ?></div>
                        <div class="text-secondary small"><?php echo number_format($t['cargo_weight']); ?> kg</div>
                    </td>
                    <td>
                        <div class="small">Start: <?php echo date('Y-m-d H:i', strtotime($t['start_date'])); ?></div>
                        <div class="small text-muted">Expected: <?php echo date('Y-m-d H:i', strtotime($t['expected_end_date'])); ?></div>
                    </td>
                    <td>
                        <div class="fw-bold text-success"><?php echo CURRENCY_SYMBOL . number_format($t['revenue'], 2); ?></div>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'bg-secondary';
                        if ($t['status'] === 'Draft') $badgeClass = 'bg-secondary text-light';
                        elseif ($t['status'] === 'Dispatched') $badgeClass = 'bg-primary';
                        elseif ($t['status'] === 'Completed') $badgeClass = 'bg-success';
                        elseif ($t['status'] === 'Cancelled') $badgeClass = 'bg-danger';
                        ?>
                        <span class="badge badge-premium <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($t['status']); ?></span>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            <button class="btn btn-outline-info btn-sm rounded-circle" onclick="viewTimeline(<?php echo $t['id']; ?>, '<?php echo $t['trip_number']; ?>')" title="View Timeline logs">
                                <i class="fas fa-history"></i>
                            </button>
                            
                            <?php if ($isDispatcherOrAdmin): ?>
                                <?php if ($t['status'] === 'Draft'): ?>
                                    <button class="btn btn-outline-primary btn-sm rounded-circle" onclick="openEditModal(<?php echo $t['id']; ?>)" title="Edit Planning">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm rounded-circle" onclick="dispatchTrip(<?php echo $t['id']; ?>)" title="Dispatch Vehicle">
                                        <i class="fas fa-play"></i>
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm rounded-circle" onclick="deleteTrip(<?php echo $t['id']; ?>)" title="Delete Draft Record">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                <?php elseif ($t['status'] === 'Dispatched'): ?>
                                    <button class="btn btn-success btn-sm rounded-circle" onclick="completeTrip(<?php echo $t['id']; ?>)" title="Complete Trip">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm rounded-circle" onclick="cancelTrip(<?php echo $t['id']; ?>)" title="Cancel Trip">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Trip Save Form Modal -->
<?php if ($isDispatcherOrAdmin): ?>
<div class="modal fade" id="tripModal" tabindex="-1" aria-labelledby="tripModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="tripModalLabel">Plan New Route Trip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="tripForm" action="<?php echo BASE_URL; ?>/trips/create" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="trip_id" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="source" class="form-label required-field">Source / Origin Location</label>
                            <input type="text" class="form-control" id="source" name="source" placeholder="Dallas Warehouse A" required>
                        </div>
                        <div class="col-md-6">
                            <label for="destination" class="form-label required-field">Destination Location</label>
                            <input type="text" class="form-control" id="destination" name="destination" placeholder="Houston Fulfillment Center" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="vehicle_id" class="form-label required-field">Assign Available Vehicle</label>
                            <select class="form-select font-monospace" id="vehicle_id" name="vehicle_id" required>
                                <option value="">-- Select Available Vehicle --</option>
                                <?php foreach ($vehicles as $v): ?>
                                    <option value="<?php echo $v['id']; ?>" data-capacity="<?php echo $v['capacity']; ?>">
                                        <?php echo htmlspecialchars($v['registration_number'] . ' - ' . $v['name'] . ' (Max: ' . number_format($v['capacity']) . ' kg)'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="driver_id" class="form-label required-field">Assign Available Driver</label>
                            <select class="form-select" id="driver_id" name="driver_id" required>
                                <option value="">-- Select Available Driver --</option>
                                <?php foreach ($drivers as $d): ?>
                                    <option value="<?php echo $d['id']; ?>">
                                        <?php echo htmlspecialchars($d['name'] . ' (Score: ' . $d['safety_score'] . '%)'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="cargo_type" class="form-label required-field">Cargo Description</label>
                            <input type="text" class="form-control" id="cargo_type" name="cargo_type" placeholder="Electronics Parts" required>
                        </div>
                        <div class="col-md-4">
                            <label for="cargo_weight" class="form-label required-field">Cargo Weight (kg)</label>
                            <input type="number" class="form-control" id="cargo_weight" name="cargo_weight" min="1" placeholder="8500" required>
                        </div>
                        <div class="col-md-4">
                            <label for="planned_distance" class="form-label required-field">Route Distance (km)</label>
                            <input type="number" class="form-control" id="planned_distance" name="planned_distance" min="1" placeholder="240" required>
                        </div>

                        <div class="col-md-6">
                            <label for="start_date" class="form-label required-field">Start Date / Time</label>
                            <input type="datetime-local" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="expected_end_date" class="form-label required-field">Expected Delivery Date / Time</label>
                            <input type="datetime-local" class="form-control" id="expected_end_date" name="expected_end_date" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="revenue" class="form-label">Estimated Revenue ($)</label>
                            <input type="number" step="0.01" class="form-control" id="revenue" name="revenue" placeholder="1200" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label" id="statusLabel">Initial Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="Draft">Save as Draft Plan</option>
                                <option value="Dispatched">Dispatch Instantly</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Trip</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Timeline Log View Modal -->
<div class="modal fade" id="timelineModal" tabindex="-1" aria-labelledby="timelineModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-start">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="timelineModalLabel">Trip Log Timeline</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="timelineContainer" class="trip-timeline">
                    <!-- Loaded dynamically via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    AjaxOps.initTable('#tripsTable');
    
    <?php if ($isDispatcherOrAdmin): ?>
        AjaxOps.submitForm(document.getElementById('tripForm'), function() {
            $('#tripModal').modal('hide');
            setTimeout(() => window.location.reload(), 1000);
        });
    <?php endif; ?>
});

// View logs
function viewTimeline(tripId, tripNumber) {
    document.getElementById('timelineModalLabel').innerText = `Timeline Logs for ${tripNumber}`;
    const container = document.getElementById('timelineContainer');
    container.innerHTML = '<div class="text-center p-3"><span class="spinner-border text-primary" role="status"></span></div>';
    
    const timelineModal = new bootstrap.Modal(document.getElementById('timelineModal'));
    timelineModal.show();

    fetch(BASE_URL + '/trips/logs?id=' + tripId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data.length > 0) {
            let html = '';
            data.data.forEach(log => {
                let statusClass = 'completed';
                if (log.status === 'Draft') statusClass = 'draft text-muted';
                else if (log.status === 'Dispatched') statusClass = 'active';
                else if (log.status === 'Cancelled') statusClass = 'cancelled';
                
                html += `
                    <div class="timeline-step ${statusClass}">
                        <div class="timeline-dot"></div>
                        <div class="ms-3 mb-1">
                            <span class="badge badge-premium ${log.status === 'Draft' ? 'bg-secondary' : (log.status === 'Dispatched' ? 'bg-primary' : (log.status === 'Completed' ? 'bg-success' : 'bg-danger'))} mb-1">${log.status}</span>
                            <div class="fw-semibold small">${log.notes || 'No comments'}</div>
                            <div class="text-secondary small mt-0.5" style="font-size: 0.75rem;">
                                <i class="far fa-clock"></i> ${log.created_at} &bull; <i class="far fa-user"></i> ${log.user_name}
                            </div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center p-3 text-muted">No timeline logs found for this trip.</div>';
        }
    })
    .catch(err => {
        container.innerHTML = '<div class="text-center p-3 text-danger">Error loading timeline.</div>';
    });
}

<?php if ($isDispatcherOrAdmin): ?>
const tripModal = new bootstrap.Modal(document.getElementById('tripModal'));
const tripForm = document.getElementById('tripForm');

function openAddModal() {
    tripForm.reset();
    document.getElementById('trip_id').value = '';
    document.getElementById('tripModalLabel').innerText = 'Plan New Route Trip';
    document.getElementById('statusLabel').classList.remove('d-none');
    document.getElementById('status').classList.remove('d-none');
    tripForm.setAttribute('action', BASE_URL + '/trips/create');
    
    // Clear dynamic temp options from dropdowns
    cleanDropdowns();
    
    tripModal.show();
}

function openEditModal(id) {
    tripForm.reset();
    document.getElementById('tripModalLabel').innerText = 'Modify Route Planning';
    // Don't show status selector when editing, only allow editing draft values
    document.getElementById('statusLabel').classList.add('d-none');
    document.getElementById('status').classList.add('d-none');
    tripForm.setAttribute('action', BASE_URL + '/trips/update');
    
    cleanDropdowns();

    AjaxOps.editLoad(BASE_URL + '/trips/get?id=' + id, function(data) {
        document.getElementById('trip_id').value = data.id;
        document.getElementById('source').value = data.source;
        document.getElementById('destination').value = data.destination;
        document.getElementById('cargo_type').value = data.cargo_type;
        document.getElementById('cargo_weight').value = data.cargo_weight;
        document.getElementById('planned_distance').value = data.planned_distance;
        document.getElementById('revenue').value = data.revenue;
        
        // Convert datetime to ISO-local format for input fields
        document.getElementById('start_date').value = data.start_date.replace(' ', 'T').slice(0, 16);
        document.getElementById('expected_end_date').value = data.expected_end_date.replace(' ', 'T').slice(0, 16);

        // Prepend current vehicle & driver to select inputs since they are not in the "Available" lists
        const vSelect = document.getElementById('vehicle_id');
        const vOption = document.createElement('option');
        vOption.value = data.vehicle_id;
        vOption.text = `${data.registration_number} - ${data.vehicle_name} (Current)`;
        vOption.className = 'temp-opt';
        vOption.selected = true;
        vSelect.add(vOption, vSelect.firstChild);

        const dSelect = document.getElementById('driver_id');
        const dOption = document.createElement('option');
        dOption.value = data.driver_id;
        dOption.text = `${data.driver_name} (Current)`;
        dOption.className = 'temp-opt';
        dOption.selected = true;
        dSelect.add(dOption, dSelect.firstChild);

        tripModal.show();
    });
}

function cleanDropdowns() {
    document.querySelectorAll('.temp-opt').forEach(opt => opt.remove());
}

function dispatchTrip(id) {
    Swal.fire({
        title: 'Dispatch Vehicle & Driver?',
        text: "This changes the Vehicle & Driver statuses to 'On Trip' and begins execution.",
        icon: 'question',
        input: 'text',
        inputPlaceholder: 'Enter dispatch comments (optional)',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        confirmButtonText: 'Yes, Dispatch Now!'
    }).then((result) => {
        if (result.isConfirmed) {
            AjaxOps.showSpinner();
            fetch(BASE_URL + '/trips/dispatch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: id, notes: result.value || 'Vehicle dispatched.' })
            })
            .then(res => res.json())
            .then(data => {
                AjaxOps.hideSpinner();
                if (data.success) {
                    AjaxOps.toast(data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    Swal.fire('Failed', data.error || 'Unable to dispatch.', 'error');
                }
            });
        }
    });
}

function completeTrip(id) {
    Swal.fire({
        title: 'Complete Active Trip?',
        text: "Ensure the cargo is delivered. The Vehicle & Driver will return to 'Available' status.",
        icon: 'success',
        html: `
            <input type="number" id="finalOdometer" class="swal2-input" placeholder="Current Odometer Reading (km)">
            <input type="text" id="compNotes" class="swal2-input" placeholder="Completion comments...">
        `,
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        confirmButtonText: 'Yes, Mark Complete!',
        preConfirm: () => {
            return {
                odometer: document.getElementById('finalOdometer').value,
                notes: document.getElementById('compNotes').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const finalData = result.value;
            AjaxOps.showSpinner();
            fetch(BASE_URL + '/trips/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ 
                    id: id, 
                    odometer: finalData.odometer, 
                    notes: finalData.notes || 'Trip completed successfully.' 
                })
            })
            .then(res => res.json())
            .then(data => {
                AjaxOps.hideSpinner();
                if (data.success) {
                    AjaxOps.toast(data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    Swal.fire('Failed', data.error || 'Unable to complete trip.', 'error');
                }
            });
        }
    });
}

function cancelTrip(id) {
    Swal.fire({
        title: 'Cancel Active Trip?',
        text: "This releases the assigned vehicle and driver back to 'Available' status.",
        icon: 'warning',
        input: 'text',
        inputPlaceholder: 'Enter cancellation reason details',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, Cancel Trip!'
    }).then((result) => {
        if (result.isConfirmed) {
            AjaxOps.showSpinner();
            fetch(BASE_URL + '/trips/cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ id: id, notes: result.value || 'Trip cancelled by dispatcher.' })
            })
            .then(res => res.json())
            .then(data => {
                AjaxOps.hideSpinner();
                if (data.success) {
                    AjaxOps.toast(data.message);
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    Swal.fire('Failed', data.error || 'Unable to cancel trip.', 'error');
                }
            });
        }
    });
}

function deleteTrip(id) {
    AjaxOps.deleteConfirm(BASE_URL + '/trips/delete', id, function() {
        setTimeout(() => window.location.reload(), 1000);
    });
}
<?php endif; ?>
</script>
