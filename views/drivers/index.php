<?php
// TransitOps Drivers Module View
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';
$isSafetyOrManager = AuthHelper::hasPermission('drivers_manage');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">Drivers Register</h3>
        <p class="text-secondary small mb-0">List of personnel authorized to operate fleet vehicles</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if ($isSafetyOrManager): ?>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-user-plus me-2"></i> Add Driver
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Driver Statistics -->
<div class="row g-3 mb-4">
    <?php
    $counts = ['Available' => 0, 'On Trip' => 0, 'Off Duty' => 0, 'Suspended' => 0];
    $total = count($drivers);
    $avgScore = 0;
    $sumScore = 0;
    
    foreach ($drivers as $d) {
        if (isset($counts[$d['status']])) $counts[$d['status']]++;
        $sumScore += $d['safety_score'];
    }
    if ($total > 0) {
        $avgScore = round($sumScore / $total, 1);
    }
    ?>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-success py-3">
            <div class="kpi-title text-success">Available Drivers</div>
            <div class="kpi-value text-success"><?php echo $counts['Available']; ?></div>
            <i class="fas fa-user-check kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-primary py-3">
            <div class="kpi-title text-primary">On Trip</div>
            <div class="kpi-value text-primary"><?php echo $counts['On Trip']; ?></div>
            <i class="fas fa-truck-container kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-info py-3">
            <div class="kpi-title text-info">Average Safety Score</div>
            <div class="kpi-value text-info"><?php echo $avgScore; ?>%</div>
            <i class="fas fa-shield-alt kpi-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-danger py-3">
            <div class="kpi-title text-danger">Suspended</div>
            <div class="kpi-value text-danger"><?php echo $counts['Suspended']; ?></div>
            <i class="fas fa-user-slash kpi-icon"></i>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="premium-card">
    <div class="table-responsive">
        <table id="driversTable" class="table table-hover w-100 align-middle">
            <thead>
                <tr>
                    <th style="width: 50px;">Photo</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Contact Info</th>
                    <th>License Details</th>
                    <th>Safety Score</th>
                    <th>Status</th>
                    <th style="width: 100px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($drivers as $d): ?>
                <tr>
                    <td>
                        <?php if ($d['photo_path']): ?>
                            <img src="<?php echo BASE_URL . '/' . htmlspecialchars($d['photo_path']); ?>" alt="Driver Photo" class="rounded-circle object-fit-cover" style="width: 40px; height: 40px; border: 1px solid var(--border-color);">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; border: 1px solid var(--border-color);">
                                <i class="fas fa-user text-secondary fs-5"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-bold text-primary font-monospace"><?php echo htmlspecialchars($d['employee_id']); ?></span>
                    </td>
                    <td>
                        <div class="fw-semibold"><?php echo htmlspecialchars($d['name']); ?></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($d['experience']); ?> yrs experience</div>
                    </td>
                    <td>
                        <div class="small"><i class="fas fa-phone text-muted me-1"></i> <?php echo htmlspecialchars($d['mobile']); ?></div>
                        <div class="small text-muted"><i class="fas fa-envelope text-muted me-1"></i> <?php echo htmlspecialchars($d['email']); ?></div>
                    </td>
                    <td>
                        <div class="fw-medium small"><?php echo htmlspecialchars($d['license_number']); ?> (<?php echo htmlspecialchars($d['license_category']); ?>)</div>
                        <?php
                        $daysToLic = (strtotime($d['license_expiry']) - time()) / 86400;
                        ?>
                        <div class="small mt-1">
                            <i class="far fa-calendar-alt text-muted me-1"></i> Expiry: 
                            <?php if ($daysToLic < 0): ?>
                                <span class="badge bg-danger-subtle text-danger fw-semibold">Expired</span>
                            <?php elseif ($daysToLic <= 30): ?>
                                <span class="badge bg-warning-subtle text-warning fw-semibold"><?php echo ceil($daysToLic); ?>d left</span>
                            <?php else: ?>
                                <span class="text-success small"><?php echo date('Y-m-d', strtotime($d['license_expiry'])); ?></span>
                            <?php endif; ?>
                            
                            <?php if ($d['license_path']): ?>
                                <a href="<?php echo BASE_URL . '/' . htmlspecialchars($d['license_path']); ?>" target="_blank" class="ms-2 doc-thumbnail text-decoration-none" title="View Uploaded License">
                                    <i class="fas fa-file-pdf text-danger"></i> View
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2" style="min-width: 120px;">
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <?php
                                $score = floatval($d['safety_score']);
                                $barColor = 'bg-danger';
                                if ($score >= 90) $barColor = 'bg-success';
                                elseif ($score >= 80) $barColor = 'bg-warning';
                                ?>
                                <div class="progress-bar <?php echo $barColor; ?>" role="progressbar" style="width: <?php echo $score; ?>%" aria-valuenow="<?php echo $score; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="small fw-bold"><?php echo $score; ?></span>
                        </div>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'bg-secondary';
                        if ($d['status'] === 'Available') $badgeClass = 'bg-success';
                        elseif ($d['status'] === 'On Trip') $badgeClass = 'bg-primary';
                        elseif ($d['status'] === 'Off Duty') $badgeClass = 'bg-dark text-light';
                        elseif ($d['status'] === 'Suspended') $badgeClass = 'bg-danger';
                        ?>
                        <span class="badge badge-premium <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($d['status']); ?></span>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <?php if ($isSafetyOrManager): ?>
                            <button class="btn btn-outline-primary btn-sm rounded-circle" onclick="openEditModal(<?php echo $d['id']; ?>)" title="Edit Driver Details">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-circle" onclick="deleteDriver(<?php echo $d['id']; ?>)" title="Delete Driver">
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

<!-- Driver Save Modal Form -->
<?php if ($isSafetyOrManager): ?>
<div class="modal fade" id="driverModal" tabindex="-1" aria-labelledby="driverModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="driverModalLabel">Register New Driver</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="driverForm" action="<?php echo BASE_URL; ?>/drivers/create" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="driver_id" name="id">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label required-field">Driver Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Robert Johnson" required>
                        </div>
                        <div class="col-md-6">
                            <label for="employee_id" class="form-label required-field">Employee ID</label>
                            <input type="text" class="form-control font-monospace" id="employee_id" name="employee_id" placeholder="EMP-002" required>
                        </div>
                        <div class="col-md-6">
                            <label for="license_number" class="form-label required-field">License Number</label>
                            <input type="text" class="form-control" id="license_number" name="license_number" placeholder="DL-1100223" required>
                        </div>
                        <div class="col-md-3">
                            <label for="license_category" class="form-label required-field">License Category</label>
                            <input type="text" class="form-control" id="license_category" name="license_category" placeholder="Heavy Commercial" required>
                        </div>
                        <div class="col-md-3">
                            <label for="license_expiry" class="form-label required-field">License Expiry Date</label>
                            <input type="date" class="form-control" id="license_expiry" name="license_expiry" required>
                        </div>
                        <div class="col-md-6">
                            <label for="mobile" class="form-label required-field">Mobile Number</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" placeholder="+1-555-0199" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label required-field">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="robert.j@company.com" required>
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Residential Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2" placeholder="456 Oak Avenue, Houston, TX"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="safety_score" class="form-label">Safety Score (%)</label>
                            <input type="number" step="0.01" class="form-control" id="safety_score" name="safety_score" min="0" max="100" value="100.00" required>
                        </div>
                        <div class="col-md-4">
                            <label for="experience" class="form-label">Experience (Years)</label>
                            <input type="number" class="form-control" id="experience" name="experience" min="0" placeholder="5" required>
                        </div>
                        <div class="col-md-4">
                            <label for="emergency_contact" class="form-label">Emergency Contact Info</label>
                            <input type="text" class="form-control" id="emergency_contact" name="emergency_contact" placeholder="Jane Doe (+1-555-0198)" required>
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="Available">Available</option>
                                <option value="On Trip">On Trip</option>
                                <option value="Off Duty">Off Duty</option>
                                <option value="Suspended">Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="photo" class="form-label">Driver Photo</label>
                            <input class="form-control" type="file" id="photo" name="photo" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label for="license_doc" class="form-label">License Scan Document</label>
                            <input class="form-control" type="file" id="license_doc" name="license_doc" accept="image/*,application/pdf">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable
    AjaxOps.initTable('#driversTable');
    
    // Bind Form Submission if user is safety or manager
    <?php if ($isSafetyOrManager): ?>
        AjaxOps.submitForm(document.getElementById('driverForm'), function() {
            $('#driverModal').modal('hide');
            setTimeout(() => window.location.reload(), 1000);
        });
    <?php endif; ?>
});

<?php if ($isSafetyOrManager): ?>
const driverModal = new bootstrap.Modal(document.getElementById('driverModal'));
const driverForm = document.getElementById('driverForm');

function openAddModal() {
    driverForm.reset();
    document.getElementById('driver_id').value = '';
    document.getElementById('driverModalLabel').innerText = 'Register New Driver';
    driverForm.setAttribute('action', BASE_URL + '/drivers/create');
    driverModal.show();
}

function openEditModal(id) {
    driverForm.reset();
    document.getElementById('driverModalLabel').innerText = 'Modify Driver Profile';
    driverForm.setAttribute('action', BASE_URL + '/drivers/update');

    AjaxOps.editLoad(BASE_URL + '/drivers/get?id=' + id, function(data) {
        document.getElementById('driver_id').value = data.id;
        document.getElementById('name').value = data.name;
        document.getElementById('employee_id').value = data.employee_id;
        document.getElementById('license_number').value = data.license_number;
        document.getElementById('license_category').value = data.license_category;
        document.getElementById('license_expiry').value = data.license_expiry;
        document.getElementById('mobile').value = data.mobile;
        document.getElementById('email').value = data.email;
        document.getElementById('address').value = data.address;
        document.getElementById('safety_score').value = data.safety_score;
        document.getElementById('experience').value = data.experience;
        document.getElementById('emergency_contact').value = data.emergency_contact;
        document.getElementById('status').value = data.status;
        
        driverModal.show();
    });
}

function deleteDriver(id) {
    AjaxOps.deleteConfirm(BASE_URL + '/drivers/delete', id, function() {
        setTimeout(() => window.location.reload(), 1000);
    });
}
<?php endif; ?>
</script>
