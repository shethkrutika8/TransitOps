<?php
// TransitOps Print/PDF Report Layout
if (!defined('SECURE_ACCESS')) exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report - <?php echo htmlspecialchars(ucfirst($reportType)); ?> | TransitOps</title>
    <!-- Bootstrap 5 CSS (needed for table structure) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #000000;
            padding: 2rem;
        }
        .report-header {
            border-bottom: 2px solid #000000;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .table th {
            background-color: #f8f9fa !important;
            color: #000000 !important;
            border-bottom: 2px solid #dee2e6 !important;
        }
        .print-footer {
            margin-top: 3rem;
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
            font-size: 0.8rem;
            color: #6c757d;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container-fluid">
        <!-- Print Header -->
        <div class="report-header d-flex justify-content-between align-items-end">
            <div>
                <h1 class="fw-bold mb-1">TransitOps Logistics</h1>
                <p class="text-muted mb-0">Smart Transport Operations Management System</p>
            </div>
            <div class="text-end">
                <h4 class="mb-1 text-capitalize"><?php echo htmlspecialchars($reportType); ?> Report</h4>
                <p class="text-muted mb-0 small">Generated: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>

        <!-- Print Actions (hidden in actual print printout) -->
        <div class="no-print mb-4 p-2 bg-light rounded d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm"><i class="fas fa-print"></i> Trigger Print</button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm">Close Window</button>
        </div>

        <!-- Table Data -->
        <table class="table table-bordered table-striped table-sm align-middle mt-4">
            <?php if ($reportType === 'roi'): ?>
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Vehicle Name</th>
                        <th>Purchase Cost</th>
                        <th>Trips Revenue</th>
                        <th>Operational Cost</th>
                        <th>Net Profit</th>
                        <th>ROI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td class="font-monospace fw-bold"><?php echo htmlspecialchars($r['registration_number']); ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['purchase_cost'], 2); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['total_revenue'], 2); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['total_cost'], 2); ?></td>
                        <td class="fw-bold"><?php echo ($r['net_profit'] < 0 ? '-' : '') . CURRENCY_SYMBOL . number_format(abs($r['net_profit']), 2); ?></td>
                        <td><?php echo number_format($r['roi_pct'], 2); ?>%</td>
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
                        <td class="font-monospace fw-bold"><?php echo htmlspecialchars($r['registration_number']); ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo number_format($r['total_fuel'], 2); ?> L</td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['total_cost'], 2); ?></td>
                        <td><?php echo number_format($r['avg_mileage'], 2); ?> km/L</td>
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
                        <th>Revenue Yield</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td class="font-monospace fw-bold"><?php echo htmlspecialchars($r['employee_id']); ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo $r['safety_score']; ?>%</td>
                        <td><?php echo $r['trips_count']; ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['total_revenue'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php elseif ($reportType === 'maintenance'): ?>
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Vehicle Name</th>
                        <th>Service Tickets</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportData as $r): ?>
                    <tr>
                        <td class="font-monospace fw-bold"><?php echo htmlspecialchars($r['registration_number']); ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo $r['tickets_count']; ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['total_maintenance'], 2); ?></td>
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
                        <td class="font-monospace fw-bold"><?php echo htmlspecialchars($r['registration_number']); ?></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['expenses_cost'], 2); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['maintenance_cost'], 2); ?></td>
                        <td><?php echo CURRENCY_SYMBOL . number_format($r['fuel_cost'], 2); ?></td>
                        <td class="fw-bold"><?php echo CURRENCY_SYMBOL . number_format($r['total_cost'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
        </table>

        <!-- Print Footer -->
        <div class="print-footer d-flex justify-content-between align-items-center">
            <span>Generated dynamically by TransitOps Smart Transport Operations Dashboard.</span>
            <span>Page 1 of 1</span>
        </div>
    </div>

</body>
</html>
