/**
 * TransitOps Dashboard Charts
 * Chart.js configuration and initialization for all dashboard charts
 */

const CHART_COLORS = {
    primary   : '#4f46e5',
    success   : '#22c55e',
    warning   : '#f59e0b',
    danger    : '#ef4444',
    info      : '#06b6d4',
    secondary : '#64748b',
    purple    : '#a855f7',
    pink      : '#ec4899',
};

Chart.defaults.color = '#94a3b8';
Chart.defaults.borderColor = 'rgba(255,255,255,0.06)';
Chart.defaults.font.family = "'Inter', 'Outfit', sans-serif";
Chart.defaults.font.size = 12;

/**
 * Utility: shared doughnut chart options
 */
function doughnutOpts(label) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { padding: 12, boxWidth: 14, color: '#94a3b8' } },
            tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                }
            }
        },
        cutout: '62%',
        animation: { animateScale: true, duration: 800 }
    };
}

/**
 * Main initialization function — called from dashboard view with PHP chart data
 */
function initDashboardCharts(data) {

    /* 1. Vehicle Status Doughnut */
    const vehicleStatusCtx = document.getElementById('vehicleStatusChart');
    if (vehicleStatusCtx) {
        new Chart(vehicleStatusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.vehicles),
                datasets: [{
                    data: Object.values(data.vehicles),
                    backgroundColor: [
                        CHART_COLORS.success,
                        CHART_COLORS.primary,
                        CHART_COLORS.warning,
                        CHART_COLORS.secondary
                    ],
                    borderWidth: 3,
                    borderColor: '#1e1e2e',
                    hoverOffset: 6
                }]
            },
            options: doughnutOpts('Vehicles')
        });
    }

    /* 2. Driver Status Doughnut */
    const driverStatusCtx = document.getElementById('driverStatusChart');
    if (driverStatusCtx) {
        new Chart(driverStatusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.drivers),
                datasets: [{
                    data: Object.values(data.drivers),
                    backgroundColor: [
                        CHART_COLORS.success,
                        CHART_COLORS.info,
                        CHART_COLORS.secondary,
                        CHART_COLORS.danger
                    ],
                    borderWidth: 3,
                    borderColor: '#1e1e2e',
                    hoverOffset: 6
                }]
            },
            options: doughnutOpts('Drivers')
        });
    }

    /* 3. Trip Status Doughnut */
    const tripStatusCtx = document.getElementById('tripStatusChart');
    if (tripStatusCtx) {
        new Chart(tripStatusCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.trips),
                datasets: [{
                    data: Object.values(data.trips),
                    backgroundColor: [
                        CHART_COLORS.secondary,
                        CHART_COLORS.primary,
                        CHART_COLORS.success,
                        CHART_COLORS.danger
                    ],
                    borderWidth: 3,
                    borderColor: '#1e1e2e',
                    hoverOffset: 6
                }]
            },
            options: doughnutOpts('Trips')
        });
    }

    /* 4. Fuel vs Maintenance Line Chart (6 months) */
    const fuelMaintCtx = document.getElementById('fuelMaintenanceChart');
    if (fuelMaintCtx) {
        new Chart(fuelMaintCtx, {
            type: 'bar',
            data: {
                labels: data.financials.months,
                datasets: [
                    {
                        label: 'Fuel Cost (₹)',
                        data: data.financials.fuel,
                        backgroundColor: 'rgba(239,68,68,0.65)',
                        borderColor: CHART_COLORS.danger,
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: CHART_COLORS.danger
                    },
                    {
                        label: 'Maintenance Cost (₹)',
                        data: data.financials.maintenance,
                        backgroundColor: 'rgba(245,158,11,0.65)',
                        borderColor: CHART_COLORS.warning,
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverBackgroundColor: CHART_COLORS.warning
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#94a3b8', boxWidth: 14 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ₹${ctx.parsed.y.toLocaleString()}`
                        }
                    }
                },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                    y: {
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: {
                            color: '#94a3b8',
                            callback: val => '₹' + (val >= 1000 ? (val/1000).toFixed(0) + 'k' : val)
                        },
                        beginAtZero: true
                    }
                },
                animation: { duration: 900 }
            }
        });
    }

    /* 5. Fleet Utilization Line Chart (6 months) */
    const utilCtx = document.getElementById('fleetUtilizationChart');
    if (utilCtx) {
        new Chart(utilCtx, {
            type: 'line',
            data: {
                labels: data.utilization.months,
                datasets: [{
                    label: 'Fleet Utilization (%)',
                    data: data.utilization.rates,
                    fill: true,
                    backgroundColor: 'rgba(79,70,229,0.15)',
                    borderColor: CHART_COLORS.primary,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: CHART_COLORS.primary,
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#94a3b8', boxWidth: 14 } },
                    tooltip: {
                        callbacks: {
                            label: ctx => ` Utilization: ${ctx.parsed.y}%`
                        }
                    }
                },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
                    y: {
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#94a3b8', callback: val => val + '%' },
                        beginAtZero: true,
                        max: 100
                    }
                },
                animation: { duration: 900 }
            }
        });
    }
}
