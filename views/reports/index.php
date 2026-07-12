<?php
// TransitOps Reports Main View
if (!defined('SECURE_ACCESS')) exit;
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h3 class="fw-bold mb-0">Operational Reporting</h3>
        <p class="text-secondary small mb-0">Analyze fleet metrics, financials, fuel logs, vehicle return-on-investments, and driver scores</p>
    </div>
</div>

<!-- Select Report Category Tab Header -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="premium-card py-2 px-3 d-flex flex-wrap gap-2 align-items-center bg-body-tertiary">
            <span class="text-secondary small fw-bold me-2 uppercase">Report:</span>
            <a href="<?php echo BASE_URL; ?>/reports?type=roi" class="btn <?php echo $reportType === 'roi' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
                <i class="fas fa-hand-holding-usd me-1"></i> Vehicle ROI
            </a>
            <a href="<?php echo BASE_URL; ?>/reports?type=fuel" class="btn <?php echo $reportType === 'fuel' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
                <i class="fas fa-gas-pump me-1"></i> Fuel Efficiency
            </a>
            <a href="<?php echo BASE_URL; ?>/reports?type=driver" class="btn <?php echo $reportType === 'driver' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
                <i class="fas fa-user-shield me-1"></i> Driver Performance
            </a>
            <a href="<?php echo BASE_URL; ?>/reports?type=maintenance" class="btn <?php echo $reportType === 'maintenance' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
                <i class="fas fa-tools me-1"></i> Maintenance spend
            </a>
            <a href="<?php echo BASE_URL; ?>/reports?type=expense" class="btn <?php echo $reportType === 'expense' ? 'btn-primary' : 'btn-outline-secondary'; ?> btn-sm">
                <i class="fas fa-file-invoice-dollar"></i> Vehicle Cost Summary
            </a>
        </div>
    </div>
</div>

<!-- Table Card & Actions -->
<div class="premium-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0 text-capitalize">
            <?php 
            if ($reportType === 'roi') echo 'Vehicle ROI & Profitability Report';
            elseif ($reportType === 'fuel') echo 'Fuel consumption & Mileage Analysis';
            elseif ($reportType === 'driver') echo 'Driver Safety & Revenue Yield';
            elseif ($reportType === 'maintenance') echo 'Maintenance Cost Distribution';
            elseif ($reportType === 'expense') echo 'Vehicle-wise Operational Spend';
            ?>
        </h5>
        
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/reports/generate?type=<?php echo $reportType; ?>&format=csv" class="btn btn-outline-success btn-sm">
                <i class="fas fa-file-excel me-1"></i> Export Excel/CSV
            </a>
            <a href="<?php echo BASE_URL; ?>/reports/generate?type=<?php echo $reportType; ?>&format=print" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="fas fa-print me-1"></i> Print / PDF
            </a>
        </div>
    </div>

    <div class="table-responsive mt-3">
        <table id="reportsTable" class="table table-striped table-hover w-100 align-middle">
            <?php if ($reportType === 'roi'): ?>
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Vehicle Name</th>
                        <th>Purchase Cost</th>
                        <th>Trips Revenue</th>
                        <th>Operational Cost</th>
                        <th>Net Profit</th>
                        <th>ROI (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td><span class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($r['registration_number']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['purchase_cost'], 2); ?></td>
                        <td class="text-success fw-semibold"><?php echo CURRENCY_SYMBOL . number_format($r['total_revenue'], 2); ?></td>
                        <td class="text-danger"><?php echo CURRENCY_SYMBOL . number_format($r['total_cost'], 2); ?></td>
                        <td class="fw-bold <?php echo $r['net_profit'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ($r['net_profit'] < 0 ? '-' : '') . CURRENCY_SYMBOL . number_format(abs($r['net_profit']), 2); ?>
                        </td>
                        <td>
                            <span class="badge <?php echo $r['roi_pct'] >= 0 ? 'bg-success' : 'bg-danger'; ?> badge-premium">
                                <?php echo number_format($r['roi_pct'], 2); ?>%
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php elseif ($reportType === 'fuel'): ?>
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Vehicle Name</th>
                        <th>Fuel Logged (L)</th>
                        <th>Total Fuel Cost</th>
                        <th>Avg Mileage Efficiency</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td><span class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($r['registration_number']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo number_format($r['total_fuel'], 2); ?> L</td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['total_cost'], 2); ?></td>
                        <td>
                            <?php 
                            $mil = floatval($r['avg_mileage']);
                            if ($mil <= 0) echo '<span class="text-muted small">No logs</span>';
                            else {
                                $color = ($mil >= 5.5) ? 'text-success fw-bold' : (($mil >= 4.0) ? 'text-warning' : 'text-danger');
                                echo '<span class="' . $color . '">' . number_format($mil, 2) . ' km/L</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php elseif ($reportType === 'driver'): ?>
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Driver Name</th>
                        <th>Safety Score</th>
                        <th>Trips Completed</th>
                        <th>Trips Revenue Generated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td><span class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($r['employee_id']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td>
                            <div class="d-flex align-items-center gap-2" style="max-width: 130px;">
                                <div class="progress flex-grow-1" style="height: 6px;">
                                    <?php 
                                    $score = floatval($r['safety_score']);
                                    $color = ($score >= 90) ? 'bg-success' : (($score >= 80) ? 'bg-warning' : 'bg-danger');
                                    ?>
                                    <div class="progress-bar <?php echo $color; ?>" role="progressbar" style="width: <?php echo $score; ?>%"></div>
                                </div>
                                <span class="fw-semibold small"><?php echo $score; ?>%</span>
                            </div>
                        </td>
                        <td><?php echo $r['trips_count']; ?> trips</td>
                        <td class="fw-bold text-success"><?php echo CURRENCY_SYMBOL . number_format($r['total_revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php elseif ($reportType === 'maintenance'): ?>
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Vehicle Name</th>
                        <th>Total Tickets Closed</th>
                        <th>Total Maintenance Spent</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td><span class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($r['registration_number']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo $r['tickets_count']; ?> log tickets</td>
                        <td class="fw-bold text-danger"><?php echo CURRENCY_SYMBOL . number_format($r['total_maintenance'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php elseif ($reportType === 'expense'): ?>
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Vehicle Name</th>
                        <th>General Expenses</th>
                        <th>Maintenance Costs</th>
                        <th>Fuel Costs</th>
                        <th>Total Operational Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td><span class="fw-bold font-monospace text-primary"><?php echo htmlspecialchars($r['registration_number']); ?></span></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['expenses_cost'], 2); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['maintenance_cost'], 2); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['fuel_cost'], 2); ?></td>
                        <td class="fw-bold text-danger"><?php echo CURRENCY_SYMBOL . number_format($r['total_cost'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    AjaxOps.initTable('#reportsTable', {
        pageLength: 25,
        order: [] // Preserve query sorting order
    });
});
</script>
