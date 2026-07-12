<?php
// TransitOps Dashboard View
if (!defined('SECURE_ACCESS')) exit;
?>

<!-- KPI Cards Row 1 -->
<div class="row g-3 mb-4">

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-primary animate-fade-in">
            <div class="kpi-title">Total Vehicles</div>
            <div class="kpi-value"><?php echo $totalVehicles; ?></div>
            <div class="text-secondary small"><?php echo $activeVehicles; ?> active in fleet</div>
            <i class="fas fa-truck kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-success animate-fade-in" style="animation-delay:0.05s">
            <div class="kpi-title">Available Vehicles</div>
            <div class="kpi-value text-success"><?php echo $vCounts['Available']; ?></div>
            <div class="text-secondary small">Ready to dispatch</div>
            <i class="fas fa-check-circle kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-info animate-fade-in" style="animation-delay:0.1s">
            <div class="kpi-title">Vehicles On Trip</div>
            <div class="kpi-value" style="color:var(--info)"><?php echo $vCounts['On Trip']; ?></div>
            <div class="text-secondary small">Currently dispatched</div>
            <i class="fas fa-shipping-fast kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-warning animate-fade-in" style="animation-delay:0.15s">
            <div class="kpi-title">Under Maintenance</div>
            <div class="kpi-value text-warning"><?php echo $vCounts['In Shop']; ?></div>
            <div class="text-secondary small">Currently in shop</div>
            <i class="fas fa-tools kpi-icon"></i>
        </div>
    </div>

</div>

<!-- KPI Cards Row 2 -->
<div class="row g-3 mb-4">

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-primary animate-fade-in" style="animation-delay:0.2s">
            <div class="kpi-title">Total Drivers</div>
            <div class="kpi-value"><?php echo $totalDrivers; ?></div>
            <div class="text-secondary small">Registered personnel</div>
            <i class="fas fa-users kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-success animate-fade-in" style="animation-delay:0.25s">
            <div class="kpi-title">Drivers Available</div>
            <div class="kpi-value text-success"><?php echo $dCounts['Available']; ?></div>
            <div class="text-secondary small">Ready to assign</div>
            <i class="fas fa-user-check kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-info animate-fade-in" style="animation-delay:0.3s">
            <div class="kpi-title">Active Trips</div>
            <div class="kpi-value" style="color:var(--info)"><?php echo $tCounts['Dispatched']; ?></div>
            <div class="text-secondary small">Currently on road</div>
            <i class="fas fa-route kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-success animate-fade-in" style="animation-delay:0.35s">
            <div class="kpi-title">Completed Today</div>
            <div class="kpi-value text-success"><?php echo $completedToday; ?></div>
            <div class="text-secondary small">Trips delivered</div>
            <i class="fas fa-flag-checkered kpi-icon"></i>
        </div>
    </div>

</div>

<!-- KPI Cards Row 3 – Financial -->
<div class="row g-3 mb-4">

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-danger animate-fade-in" style="animation-delay:0.4s">
            <div class="kpi-title">Fuel Cost</div>
            <div class="kpi-value text-danger"><?php echo CURRENCY_SYMBOL . number_format($fuelTotal, 0); ?></div>
            <div class="text-secondary small">Total fuel spend</div>
            <i class="fas fa-gas-pump kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-warning animate-fade-in" style="animation-delay:0.45s">
            <div class="kpi-title">Maintenance Cost</div>
            <div class="kpi-value text-warning"><?php echo CURRENCY_SYMBOL . number_format($maintTotal, 0); ?></div>
            <div class="text-secondary small">Total service spend</div>
            <i class="fas fa-wrench kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-danger animate-fade-in" style="animation-delay:0.5s">
            <div class="kpi-title">Total Operational Cost</div>
            <div class="kpi-value text-danger" style="font-size:1.4rem"><?php echo CURRENCY_SYMBOL . number_format($totalOpCost, 0); ?></div>
            <div class="text-secondary small">All categories combined</div>
            <i class="fas fa-wallet kpi-icon"></i>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="premium-card kpi-card kpi-primary animate-fade-in" style="animation-delay:0.55s">
            <div class="kpi-title">Fleet Utilization</div>
            <div class="kpi-value"><?php echo $fleetUtil; ?>%</div>
            <div class="progress mt-2" style="height: 6px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $fleetUtil; ?>%"></div>
            </div>
            <i class="fas fa-chart-pie kpi-icon"></i>
        </div>
    </div>

</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">

    <div class="col-12 col-lg-4">
        <div class="premium-card h-100">
            <h6 class="fw-bold mb-3">Vehicle Status Distribution</h6>
            <div style="height: 220px; position: relative;">
                <canvas id="vehicleStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="premium-card h-100">
            <h6 class="fw-bold mb-3">Driver Status Distribution</h6>
            <div style="height: 220px; position: relative;">
                <canvas id="driverStatusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="premium-card h-100">
            <h6 class="fw-bold mb-3">Trip Status Breakdown</h6>
            <div style="height: 220px; position: relative;">
                <canvas id="tripStatusChart"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Charts Row 2 -->
<div class="row g-3 mb-4">

    <div class="col-12 col-lg-6">
        <div class="premium-card">
            <h6 class="fw-bold mb-3">Monthly Fuel vs Maintenance Costs (6 months)</h6>
            <div style="height: 240px; position: relative;">
                <canvas id="fuelMaintenanceChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="premium-card">
            <h6 class="fw-bold mb-3">Fleet Utilization Trend (%)</h6>
            <div style="height: 240px; position: relative;">
                <canvas id="fleetUtilizationChart"></canvas>
            </div>
        </div>
    </div>

</div>

<!-- Widgets Row -->
<div class="row g-3">

    <!-- Recent Trips -->
    <div class="col-12 col-lg-6">
        <div class="premium-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recent Trips</h6>
                <a href="<?php echo BASE_URL; ?>/trips" class="btn btn-outline-primary btn-sm">View All</a>
            </div>
            <?php if (empty($recentTrips)): ?>
            <p class="text-muted small text-center py-3">No trips recorded yet.</p>
            <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($recentTrips as $t): ?>
                <?php
                $bc = 'bg-secondary';
                if ($t['status'] === 'Dispatched') $bc = 'bg-primary';
                elseif ($t['status'] === 'Completed') $bc = 'bg-success';
                elseif ($t['status'] === 'Cancelled') $bc = 'bg-danger';
                ?>
                <div class="d-flex align-items-center gap-3 p-2 rounded" style="background:var(--bg-primary)">
                    <div class="flex-shrink-0">
                        <span class="badge badge-premium <?php echo $bc; ?> text-nowrap"><?php echo $t['status']; ?></span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold small text-truncate"><?php echo htmlspecialchars($t['source'] . ' → ' . $t['destination']); ?></div>
                        <div class="text-secondary" style="font-size:0.75rem"><?php echo htmlspecialchars($t['registration_number'] . ' | ' . $t['driver_name']); ?></div>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="fw-bold text-success small"><?php echo CURRENCY_SYMBOL . number_format($t['revenue'], 0); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Vehicles in Maintenance -->
    <div class="col-12 col-lg-6">
        <div class="premium-card h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Vehicles In Shop</h6>
                <a href="<?php echo BASE_URL; ?>/maintenance" class="btn btn-outline-warning btn-sm">View All</a>
            </div>
            <?php if (empty($inShop)): ?>
            <p class="text-muted small text-center py-3">No vehicles currently in maintenance.</p>
            <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($inShop as $m): ?>
                <div class="d-flex align-items-center gap-3 p-2 rounded" style="background:var(--bg-primary)">
                    <div class="flex-shrink-0">
                        <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center" style="width:36px;height:36px">
                            <i class="fas fa-tools text-dark small"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold small"><?php echo htmlspecialchars($m['registration_number'] . ' – ' . $m['name']); ?></div>
                        <div class="text-secondary" style="font-size:0.75rem"><?php echo htmlspecialchars($m['type'] . ' at ' . $m['vendor']); ?></div>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="text-warning fw-bold small"><?php echo CURRENCY_SYMBOL . number_format($m['cost'], 0); ?></div>
                        <div class="text-muted" style="font-size:0.7rem"><?php echo date('M j', strtotime($m['date'])); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming Expiries -->
    <div class="col-12 col-lg-6">
        <div class="premium-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Upcoming License/Insurance Expiry</h6>
                <a href="<?php echo BASE_URL; ?>/vehicles" class="btn btn-outline-danger btn-sm">View Fleet</a>
            </div>
            <?php if (empty($upcoming)): ?>
            <p class="text-muted small text-center py-3">No documents expiring in the next 30 days. ✓</p>
            <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($upcoming as $u): ?>
                <div class="d-flex align-items-center gap-3 p-2 rounded border border-danger" style="background:rgba(239,68,68,0.04)">
                    <i class="fas fa-exclamation-triangle text-danger flex-shrink-0"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold small"><?php echo htmlspecialchars($u['registration_number'] . ' – ' . $u['name']); ?></div>
                        <div class="text-secondary" style="font-size:0.75rem">
                            Insurance: <?php echo $u['insurance_expiry']; ?> &nbsp;|&nbsp;
                            RC: <?php echo $u['rc_expiry']; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Expenses -->
    <div class="col-12 col-lg-6">
        <div class="premium-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Recent Expenses</h6>
                <a href="<?php echo BASE_URL; ?>/expenses" class="btn btn-outline-secondary btn-sm">View All</a>
            </div>
            <?php if (empty($recentExpenses)): ?>
            <p class="text-muted small text-center py-3">No expenses recorded yet.</p>
            <?php else: ?>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($recentExpenses as $e): ?>
                <div class="d-flex align-items-center gap-3 p-2 rounded" style="background:var(--bg-primary)">
                    <div class="flex-shrink-0">
                        <span class="badge bg-secondary badge-premium text-nowrap"><?php echo htmlspecialchars($e['type']); ?></span>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="small fw-medium text-truncate"><?php echo htmlspecialchars($e['remarks'] ?: 'No remarks'); ?></div>
                        <div class="text-secondary" style="font-size:0.75rem"><?php echo htmlspecialchars($e['registration_number']); ?> | <?php echo date('M j', strtotime($e['date'])); ?></div>
                    </div>
                    <div class="flex-shrink-0">
                        <div class="fw-bold text-danger small"><?php echo CURRENCY_SYMBOL . number_format($e['amount'], 0); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Inject chart data and load charts.js -->
<script src="<?php echo BASE_URL; ?>/assets/js/charts.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = <?php echo $chartData; ?>;
    initDashboardCharts(chartData);
});
</script>
