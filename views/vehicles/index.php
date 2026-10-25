<?php
// TransitOps Vehicles Module View
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';
$isManager = AuthHelper::hasPermission('vehicles_manage');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">Vehicles Fleet</h3>
        <p class="text-secondary small mb-0">Total list of transport units registered in the fleet</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if ($isManager): ?>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-plus me-2"></i> Add Vehicle
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <?php
    $counts = ['Available' => 0, 'On Trip' => 0, 'In Shop' => 0, 'Retired' => 0];
    $total = count($vehicles);
    foreach ($vehicles as $v) {
        if (isset($counts[$v['status']])) $counts[$v['status']]++;
    }
    ?>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-success py-3">
            <div class="kpi-title text-success">Available</div>
            <div class="kpi-value text-success"><?php echo $counts['Available']; ?></div>
            <i class="fas fa-check-circle kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-primary py-3">
            <div class="kpi-title text-primary">On Trip</div>
            <div class="kpi-value text-primary"><?php echo $counts['On Trip']; ?></div>
            <i class="fas fa-route kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-warning py-3">
            <div class="kpi-title text-warning">In Shop</div>
            <div class="kpi-value text-warning"><?php echo $counts['In Shop']; ?></div>
            <i class="fas fa-tools kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-danger py-3">
            <div class="kpi-title text-danger">Total Active</div>
            <div class="kpi-value text-danger"><?php echo ($total - $counts['Retired']); ?></div>
            <i class="fas fa-truck kpi-icon"></i>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="premium-card">
    <div class="table-responsive">
        <table id="vehiclesTable" class="table table-hover w-100 align-middle">
            <thead>
                <tr>
                    <th style="width: 60px;">Image</th>
                    <th>Reg Number</th>
                    <th>Vehicle Details</th>
                    <th>Capacity</th>
                    <th>Odometer</th>
                    <th>Status</th>
                    <th>Expiry Alerts</th>
                    <th style="width: 100px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $v): ?>
                <tr>
                    <td>
                        <?php if ($v['image_path']): ?>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($v['image_path']); ?>" alt="Vehicle image" class="rounded-circle object-fit-cover" style="width: 42px; height: 42px; border: 1px solid var(--border-color);">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border: 1px solid var(--border-color);">
                                <i class="fas fa-truck text-secondary fs-5"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-bold text-primary font-monospace fs-6"><?php echo htmlspecialchars($v['registration_number']); ?></span>
                    </td>
                    <td>
                        <div class="fw-semibold text-wrap"><?php echo htmlspecialchars($v['name']); ?></div>
                        <div class="text-secondary small"><?php echo htmlspecialchars($v['manufacturer'] . ' ' . $v['model'] . ' (' . $v['year'] . ')'); ?></div>
                    </td>
                    <td>
                        <div class="fw-medium"><?php echo number_format($v['capacity']); ?> kg</div>
                        <div class="text-muted small">Max Load</div>
                    </td>
                    <td>
                        <div><?php echo number_format($v['odometer']); ?> km</div>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'bg-secondary';
                        if ($v['status'] === 'Available') $badgeClass = 'bg-success';
                        elseif ($v['status'] === 'On Trip') $badgeClass = 'bg-primary';
                        elseif ($v['status'] === 'In Shop') $badgeClass = 'bg-warning text-dark';
                        elseif ($v['status'] === 'Retired') $badgeClass = 'bg-danger';
                        ?>
                        <span class="badge badge-premium <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($v['status']); ?></span>
                    </td>
                    <td>
                        <?php
                        $daysToIns = (strtotime($v['insurance_expiry']) - time()) / 86400;
                        $daysToRc = (strtotime($v['rc_expiry']) - time()) / 86400;
                        ?>
                        <div class="small d-flex flex-column gap-1">
                            <span>
                                <i class="fas fa-shield-alt text-muted me-1"></i> Ins: 
                                <?php if ($daysToIns < 0): ?>
                                    <span class="text-danger fw-semibold">Expired</span>
                                <?php elseif ($daysToIns <= 30): ?>
                                    <span class="text-warning fw-semibold"><?php echo ceil($daysToIns); ?>d left</span>
                                <?php else: ?>
                                    <span class="text-success"><?php echo date('Y-m-d', strtotime($v['insurance_expiry'])); ?></span>
                                <?php endif; ?>
                            </span>
                            <span>
                                <i class="fas fa-file-contract text-muted me-1"></i> RC: 
                                <?php if ($daysToRc < 0): ?>
                                    <span class="text-danger fw-semibold">Expired</span>
                                <?php elseif ($daysToRc <= 30): ?>
                                    <span class="text-warning fw-semibold"><?php echo ceil($daysToRc); ?>d left</span>
                                <?php else: ?>
                                    <span class="text-success"><?php echo date('Y-m-d', strtotime($v['rc_expiry'])); ?></span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <?php if ($isManager): ?>
                            <button class="btn btn-outline-primary btn-sm rounded-circle" onclick="openEditModal(<?php echo $v['id']; ?>)" title="Edit Vehicle">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-circle" onclick="deleteVehicle(<?php echo $v['id']; ?>)" title="Delete Vehicle">
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

<!-- Vehicle Save Modal Form -->
<?php if ($isManager): ?>
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="vehicleModalLabel">Add New Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="vehicleForm" action="<?php echo BASE_URL; ?>/vehicles/create" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="vehicle_id" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="registration_number" class="form-label required-field">Registration Number</label>
                            <input type="text" class="form-control font-monospace" id="registration_number" name="registration_number" placeholder="TX-1234-A" maxlength="30" required>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label">Vehicle Nickname / Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Volvo FH16" maxlength="100" required>
                        </div>
                        <div class="col-md-4">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" placeholder="Volvo" maxlength="100" required>
                        </div>
                        <div class="col-md-4">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" placeholder="FH16 Globetrotter" maxlength="100" required>
                        </div>
                        <div class="col-md-4">
                            <label for="type" class="form-label">Vehicle Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="Truck">Truck (Heavy)</option>
                                <option value="Van">Cargo Van</option>
                                <option value="Trailer">Trailer</option>
                                <option value="Car">Sedan / Service Car</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="year" class="form-label">Year of Manufacture</label>
                            <input type="number" class="form-control" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" value="<?php echo date('Y'); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="capacity" class="form-label">Load Capacity (kg)</label>
                            <input type="number" class="form-control" id="capacity" name="capacity" min="1" step="0.01" placeholder="18000" required>
                        </div>
                        <div class="col-md-4">
                            <label for="odometer" class="form-label">Odometer Reading (km)</label>
                            <input type="number" class="form-control" id="odometer" name="odometer" min="0" placeholder="0" required>
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_cost" class="form-label">Purchase Cost ($)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="purchase_cost" name="purchase_cost" placeholder="120000" required>
                        </div>
                        <div class="col-md-6">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date" max="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="insurance_expiry" class="form-label">Insurance Expiry Date</label>
                            <input type="date" class="form-control" id="insurance_expiry" name="insurance_expiry" required>
                        </div>
                        <div class="col-md-6">
                            <label for="rc_expiry" class="form-label">Registration Certificate (RC) Expiry</label>
                            <input type="date" class="form-control" id="rc_expiry" name="rc_expiry" required>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Available">Available</option>
                                <option value="On Trip">On Trip</option>
                                <option value="In Shop">In Shop</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="image" class="form-label">Vehicle Image Upload</label>
                            <input class="form-control" type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" data-max-bytes="5242880">
                            <div class="form-text small text-muted">MIME formats: JPG, PNG, WEBP. Max size: 5MB</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    AjaxOps.initTable('#vehiclesTable');
    
    // Bind AJAX Form Submissions if user has manage role
    <?php if ($isManager): ?>
        AjaxOps.submitForm(document.getElementById('vehicleForm'), function() {
            $('#vehicleModal').modal('hide');
            setTimeout(() => window.location.reload(), 1000);
        });
    <?php endif; ?>
});

<?php if ($isManager): ?>
const vehicleModal = new bootstrap.Modal(document.getElementById('vehicleModal'));
const vehicleForm = document.getElementById('vehicleForm');

function openAddModal() {
    vehicleForm.reset();
    document.getElementById('vehicle_id').value = '';
    document.getElementById('vehicleModalLabel').innerText = 'Register New Vehicle';
    vehicleForm.setAttribute('action', BASE_URL + '/vehicles/create');
    vehicleModal.show();
}

function openEditModal(id) {
    vehicleForm.reset();
    document.getElementById('vehicleModalLabel').innerText = 'Modify Vehicle Details';
    vehicleForm.setAttribute('action', BASE_URL + '/vehicles/update');

    AjaxOps.editLoad(BASE_URL + '/vehicles/get?id=' + id, function(data) {
        document.getElementById('vehicle_id').value = data.id;
        document.getElementById('registration_number').value = data.registration_number;
        document.getElementById('name').value = data.name;
        document.getElementById('manufacturer').value = data.manufacturer;
        document.getElementById('model').value = data.model;
        document.getElementById('type').value = data.type;
        document.getElementById('year').value = data.year;
        document.getElementById('capacity').value = data.capacity;
        document.getElementById('odometer').value = data.odometer;
        document.getElementById('purchase_cost').value = data.purchase_cost;
        document.getElementById('purchase_date').value = data.purchase_date;
        document.getElementById('insurance_expiry').value = data.insurance_expiry;
        document.getElementById('rc_expiry').value = data.rc_expiry;
        document.getElementById('status').value = data.status;
        
        vehicleModal.show();
    });
}

function deleteVehicle(id) {
    AjaxOps.deleteConfirm(BASE_URL + '/vehicles/delete', id, function() {
        setTimeout(() => window.location.reload(), 1000);
    });
}
<?php endif; ?>
</script>
