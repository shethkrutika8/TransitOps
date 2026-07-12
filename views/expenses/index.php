<?php
// TransitOps Expenses Module View
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';
$isManagerOrFinance = AuthHelper::hasPermission('expenses_manage');
?>

<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h3 class="fw-bold mb-0">Expense Registry</h3>
        <p class="text-secondary small mb-0">Track general operational overheads: tolls, parkings, insurances, taxes, and other charges</p>
    </div>
    <div class="col-md-6 text-md-end mt-3 mt-md-0">
        <?php if ($isManagerOrFinance): ?>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fas fa-receipt me-2"></i> Log Expense
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Total Cost Banner Widget -->
<div class="row mb-4">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="premium-card kpi-card kpi-danger">
            <div class="kpi-title text-danger">Total Operational Cost</div>
            <div class="kpi-value text-danger" style="font-size: 2.1rem;">
                <?php echo CURRENCY_SYMBOL . number_format($totalCost, 2); ?>
            </div>
            <span class="text-secondary small">(Includes General Expenses + Maintenance + Fuel Logs)</span>
            <i class="fas fa-wallet kpi-icon text-danger"></i>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="premium-card">
    <div class="table-responsive">
        <table id="expensesTable" class="table table-hover w-100 align-middle">
            <thead>
                <tr>
                    <th>Vehicle</th>
                    <th>Expense Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Remarks</th>
                    <th style="width: 100px;" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($expenses as $e): ?>
                <tr>
                    <td>
                        <div class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($e['registration_number']); ?></div>
                        <div class="text-secondary small"><?php echo htmlspecialchars($e['vehicle_name']); ?></div>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'bg-secondary';
                        if ($e['type'] === 'Toll') $badgeClass = 'bg-info text-dark';
                        elseif ($e['type'] === 'Parking') $badgeClass = 'bg-secondary text-light';
                        elseif ($e['type'] === 'Repairs') $badgeClass = 'bg-warning text-dark';
                        elseif ($e['type'] === 'Insurance') $badgeClass = 'bg-primary text-light';
                        elseif ($e['type'] === 'Tax') $badgeClass = 'bg-dark text-light';
                        ?>
                        <span class="badge badge-premium <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($e['type']); ?></span>
                    </td>
                    <td>
                        <div class="fw-bold fs-6 text-dark"><?php echo CURRENCY_SYMBOL . number_format($e['amount'], 2); ?></div>
                    </td>
                    <td>
                        <div class="small fw-semibold"><?php echo date('Y-m-d', strtotime($e['date'])); ?></div>
                    </td>
                    <td>
                        <div class="text-wrap small text-truncate-custom" style="max-width: 300px;" title="<?php echo htmlspecialchars($e['remarks']); ?>">
                            <?php echo htmlspecialchars($e['remarks'] ?? 'N/A'); ?>
                        </div>
                    </td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2">
                            <?php if ($isManagerOrFinance): ?>
                            <button class="btn btn-outline-primary btn-sm rounded-circle" onclick="openEditModal(<?php echo $e['id']; ?>)" title="Edit Log">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm rounded-circle" onclick="deleteExpense(<?php echo $e['id']; ?>)" title="Delete Log">
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

<!-- Expense Form Modal -->
<?php if ($isManagerOrFinance): ?>
<div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="expenseModalLabel">Log Operational Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="expenseForm" action="<?php echo BASE_URL; ?>/expenses/create" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="expense_id" name="id">
                    
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
                            <label for="type" class="form-label required-field">Expense Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="Toll">Toll Charges</option>
                                <option value="Parking">Parking Charges</option>
                                <option value="Repairs">Repairs & Parts</option>
                                <option value="Insurance">Insurance Policy</option>
                                <option value="Tax">Government Tax / Permits</option>
                                <option value="Other">Other Expenses</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label required-field">Expense Amount ($)</label>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" min="0.01" placeholder="45.00" required>
                        </div>
                        <div class="col-md-12">
                            <label for="date" class="form-label required-field">Billing Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>
                        <div class="col-12">
                            <label for="remarks" class="form-label">Remarks / Description</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="2" placeholder="Provide extra invoice references or vendor notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    AjaxOps.initTable('#expensesTable');
    
    <?php if ($isManagerOrFinance): ?>
        AjaxOps.submitForm(document.getElementById('expenseForm'), function() {
            $('#expenseModal').modal('hide');
            setTimeout(() => window.location.reload(), 1000);
        });
    <?php endif; ?>
});

<?php if ($isManagerOrFinance): ?>
const expenseModal = new bootstrap.Modal(document.getElementById('expenseModal'));
const expenseForm = document.getElementById('expenseForm');

function openAddModal() {
    expenseForm.reset();
    document.getElementById('expense_id').value = '';
    document.getElementById('expenseModalLabel').innerText = 'Log Operational Expense';
    expenseForm.setAttribute('action', BASE_URL + '/expenses/create');
    expenseModal.show();
}

function openEditModal(id) {
    expenseForm.reset();
    document.getElementById('expenseModalLabel').innerText = 'Modify Expense Log Entry';
    expenseForm.setAttribute('action', BASE_URL + '/expenses/update');

    AjaxOps.editLoad(BASE_URL + '/expenses/get?id=' + id, function(data) {
        document.getElementById('expense_id').value = data.id;
        document.getElementById('vehicle_id').value = data.vehicle_id;
        document.getElementById('type').value = data.type;
        document.getElementById('amount').value = data.amount;
        document.getElementById('date').value = data.date;
        document.getElementById('remarks').value = data.remarks;
        
        expenseModal.show();
    });
}

function deleteExpense(id) {
    AjaxOps.deleteConfirm(BASE_URL + '/expenses/delete', id, function() {
        setTimeout(() => window.location.reload(), 1000);
    });
}
<?php endif; ?>
</script>
