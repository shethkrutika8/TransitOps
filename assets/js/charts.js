// TransitOps Chart.js Setup

let vehiclesChart, driversChart, fuelMaintenanceChart, fleetUtilizationChart, tripsChart;

function getThemeColors() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    return {
        text: isDark ? '#9ca3af' : '#64748b',
        grid: isDark ? '#1f2937' : '#e2e8f0',
        tooltipBg: isDark ? '#1e293b' : '#ffffff',
        tooltipBorder: isDark ? '#334155' : '#e2e8f0',
        tooltipText: isDark ? '#f3f4f6' : '#1e293b'
    };
}

function initDashboardCharts(data) {
    const colors = getThemeColors();

    Chart.defaults.color = colors.text;
    Chart.defaults.borderColor = colors.grid;
    Chart.defaults.font.family = 'Outfit, sans-serif';

    // 1. Vehicle Status Doughnut Chart
    const vehicleCtx = document.getElementById('vehicleStatusChart');
    if (vehicleCtx) {
        vehiclesChart = new Chart(vehicleCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.vehicles),
                datasets: [{
                    data: Object.values(data.vehicles),
                    backgroundColor: ['#10b981', '#4f46e5', '#f59e0b', '#ef4444'],
                    borderWidth: 2,
                    borderColor: 'transparent'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
                },
                cutout: '70%'
            }
        });
    }

    // 2. Driver Status Doughnut Chart
    const driverCtx = document.getElementById('driverStatusChart');
    if (driverCtx) {
        driversChart = new Chart(driverCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.drivers),
                datasets: [{
                    data: Object.values(data.drivers),
                    backgroundColor: ['#10b981', '#4f46e5', '#6b7280', '#ef4444'],
                    borderWidth: 2,
                    borderColor: 'transparent'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
                },
                cutout: '70%'
            }
        });
    }

    // 3. Monthly Fuel vs Maintenance Grouped Bar Chart
    const fmCtx = document.getElementById('fuelMaintenanceChart');
    if (fmCtx) {
        fuelMaintenanceChart = new Chart(fmCtx, {
            type: 'bar',
            data: {
                labels: data.financials.months,
                datasets: [
                    {
                        label: 'Fuel Cost',
                        data: data.financials.fuel,
                        backgroundColor: '#06b6d4',
                        borderRadius: 6
                    },
                    {
                        label: 'Maintenance Cost',
                        data: data.financials.maintenance,
                        backgroundColor: '#f59e0b',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false } },
                    y: { grid: { color: colors.grid } }
                },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12 } }
                }
            }
        });
    }

    // 4. Fleet Utilization Trend Line Chart
    const utilCtx = document.getElementById('fleetUtilizationChart');
    if (utilCtx) {
        fleetUtilizationChart = new Chart(utilCtx, {
            type: 'line',
            data: {
                labels: data.utilization.months,
                datasets: [{
                    label: 'Utilization %',
                    data: data.utilization.rates,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.05)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: '#4f46e5',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false } },
                    y: { 
                        min: 0, 
                        max: 100, 
                        grid: { color: colors.grid },
                        ticks: { callback: value => value + '%' }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }

    // 5. Trip Status Chart (Doughnut)
    const tripCtx = document.getElementById('tripStatusChart');
    if (tripCtx) {
        tripsChart = new Chart(tripCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data.trips),
                datasets: [{
                    data: Object.values(data.trips),
                    backgroundColor: ['#6b7280', '#4f46e5', '#10b981', '#ef4444'],
                    borderWidth: 2,
                    borderColor: 'transparent'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15 } }
                },
                cutout: '70%'
            }
        });
    }

    // Handle Theme Change Live Updates
    window.addEventListener('themeChanged', function() {
        const newColors = getThemeColors();
        
        Chart.defaults.color = newColors.text;
        Chart.defaults.borderColor = newColors.grid;

        [vehiclesChart, driversChart, fuelMaintenanceChart, fleetUtilizationChart, tripsChart].forEach(chart => {
            if (!chart) return;
            
            // Update scales grid colors
            if (chart.options.scales) {
                if (chart.options.scales.y) {
                    chart.options.scales.y.grid.color = newColors.grid;
                }
            }
            chart.update();
        });
    });
}
