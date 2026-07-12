<?php
// TransitOps Fuel Module View
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';
$isManagerOrFinance = AuthHelper::hasPermission('fuel_manage');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">Fuel Operations</h3>
        <p class="text-secondary small mb-0">Log fueling sessions, station charges, odometer updates, and track engine mileage efficiency</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if ($isManagerOrFinance): ?>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-gas-pump me-2"></i> Log Fuel Stop
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Table Card -->
<div class="premium-card">
    <div class="table-responsive">
        <table id="fuelLogsTable" class="table table-hover w-100 align-middle">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Date</th>
                    <th>Quantity (L)</th>
                    <th>Fuel Cost</th>
                    <th>Fuel Station</th>
                    <th>Odometer Reading</th>
                    <th>Mileage Efficiency</th>
                    <th style="width: 100px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <div class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($log['registration_number']); ?></div>
                        <div class="text-secondary small"><?php echo htmlspecialchars($log['vehicle_name']); ?></div>
                    </td>
                    <td>
                        <div class="small fw-semibold"><?php echo date('Y-m-d', strtotime($log['date'])); ?></div>
                    </td>
                    <td>
                        <div class="fw-semibold"><?php echo number_format($log['quantity'], 2); ?> L</div>
                    </td>
                    <td>
                        <div class="fw-bold text-dark"><?php echo CURRENCY_SYMBOL . number_format($log['cost'], 2); ?></div>
                        <div class="text-muted small" style="font-size: 0.75rem;">
                            <?php echo CURRENCY_SYMBOL . number_format($log['cost'] / $log['quantity'], 2); ?>/L
                        </div>
                    </td>
                    <td>
                        <div class="small"><i class="fas fa-store-alt text-muted me-1"></i> <?php echo htmlspecialchars($log['station']); ?></div>
                    </td>
                    <td>
                        <div class="small font-monospace fw-medium"><?php echo number_format($log['odometer']); ?> km</div>
                    </td>
                    <td>
                        <?php 
                        $mil = floatval($log['mileage']);
                        if ($mil <= 0) {
                            echo '<span class="text-muted small">N/A (First Log)</span>';
                        } else {
                            $colorClass = 'text-danger fw-semibold';
                            if ($mil >= 6.0) $colorClass = 'text-success fw-bold';
                            elseif ($mil >= 4.0) $colorClass = 'text-warning fw-semibold';
                            
                            echo '<span class="' . $colorClass . '">' . number_format($mil, 2) . ' km/L</span>';
                        }
                        ?>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <?php if ($isManagerOrFinance): ?>
                            <button class="btn btn-outline-primary btn-sm rounded-circle" onclick="openEditModal(<?php echo $log['id']; ?>)" title="Edit Log">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-circle" onclick="deleteLog(<?php echo $log['id']; ?>)" title="Delete Log">
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

<!-- Fuel Log Form Modal -->
<?php if ($isManagerOrFinance): ?>
<div class="modal fade" id="fuelModal" tabindex="-1" aria-labelledby="fuelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="fuelModalLabel">Log Fueling Stop</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="fuelForm" action="<?php echo BASE_URL; ?>/fuel/create" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="fuel_id" name="id">
                    
                    <div class="row g-3">
                        <div class="col-12">
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
                            <label for="date" class="form-label required-field">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="quantity" class="form-label required-field">Fuel Quantity (Litres)</label>
                            <input type="number" step="0.01" class="form-control" id="quantity" name="quantity" min="0.01" placeholder="120.00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="cost" class="form-label required-field">Total Cost ($)</label>
                            <input type="number" step="0.01" class="form-control" id="cost" name="cost" min="0.01" placeholder="480.00" required>
                        </div>
                        <div class="col-md-6">
                            <label for="odometer" class="form-label required-field">Odometer Reading (km)</label>
                            <input type="number" class="form-control" id="odometer" name="odometer" min="1" placeholder="45200" required>
                        </div>
                        <div class="col-12">
                            <label for="station" class="form-label required-field">Fuel Station Vendor</label>
                            <input type="text" class="form-control" id="station" name="station" placeholder="Texaco Star #21" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Fuel Stop</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    AjaxOps.initTable('#fuelLogsTable');
    
    <?php if ($isManagerOrFinance): ?>
        AjaxOps.submitForm(document.getElementById('fuelForm'), function() {
            $('#fuelModal').modal('hide');
            setTimeout(() => window.location.reload(), 1000);
        });
    <?php endif; ?>
});

<?php if ($isManagerOrFinance): ?>
const fuelModal = new bootstrap.Modal(document.getElementById('fuelModal'));
const fuelForm = document.getElementById('fuelForm');

function openAddModal() {
    fuelForm.reset();
    document.getElementById('fuel_id').value = '';
    document.getElementById('fuelModalLabel').innerText = 'Log Fuel Stop';
    fuelForm.setAttribute('action', BASE_URL + '/fuel/create');
    fuelModal.show();
}

function openEditModal(id) {
    fuelForm.reset();
    document.getElementById('fuelModalLabel').innerText = 'Modify Fuel Stop Entry';
    fuelForm.setAttribute('action', BASE_URL + '/fuel/update');

    AjaxOps.editLoad(BASE_URL + '/fuel/get?id=' + id, function(data) {
        document.getElementById('fuel_id').value = data.id;
        document.getElementById('vehicle_id').value = data.vehicle_id;
        document.getElementById('date').value = data.date;
        document.getElementById('quantity').value = data.quantity;
        document.getElementById('cost').value = data.cost;
        document.getElementById('odometer').value = data.odometer;
        document.getElementById('station').value = data.station;
        
        fuelModal.show();
    });
}

function deleteLog(id) {
    AjaxOps.deleteConfirm(BASE_URL + '/fuel/delete', id, function() {
        setTimeout(() => window.location.reload(), 1000);
    });
}
<?php endif; ?>
</script>
