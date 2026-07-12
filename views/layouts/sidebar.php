<?php
// TransitOps Sidebar Layout
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';

// Detect active page based on the current URI path
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$activePage = 'dashboard';
if (strpos($currentPath, '/vehicles') !== false) $activePage = 'vehicles';
elseif (strpos($currentPath, '/drivers') !== false) $activePage = 'drivers';
elseif (strpos($currentPath, '/trips') !== false) $activePage = 'trips';
elseif (strpos($currentPath, '/maintenance') !== false) $activePage = 'maintenance';
elseif (strpos($currentPath, '/fuel') !== false) $activePage = 'fuel';
elseif (strpos($currentPath, '/expenses') !== false) $activePage = 'expenses';
elseif (strpos($currentPath, '/reports') !== false) $activePage = 'reports';
elseif (strpos($currentPath, '/settings') !== false) $activePage = 'settings';
elseif (strpos($currentPath, '/notifications') !== false) $activePage = 'notifications';
?>
<aside id="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo BASE_URL; ?>/" class="sidebar-logo">
            <i class="fas fa-truck-moving text-primary"></i>
            <span>TransitOps</span>
        </a>
    </div>
    
    <ul class="sidebar-menu">
        <!-- Dashboard (Visible to all) -->
        <li class="menu-item <?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/dashboard" class="menu-link">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- Vehicles (Super Admin, Fleet Manager, Safety, Dispatcher) -->
        <?php if (AuthHelper::hasPermission('vehicles_view') || AuthHelper::hasPermission('vehicles_manage')): ?>
        <li class="menu-item <?php echo $activePage === 'vehicles' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/vehicles" class="menu-link">
                <i class="fas fa-truck"></i>
                <span>Vehicles</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Drivers (Super Admin, Fleet Manager, Safety, Dispatcher) -->
        <?php if (AuthHelper::hasPermission('drivers_view') || AuthHelper::hasPermission('drivers_manage')): ?>
        <li class="menu-item <?php echo $activePage === 'drivers' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/drivers" class="menu-link">
                <i class="fas fa-id-card"></i>
                <span>Drivers</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Trips (Super Admin, Fleet Manager, Dispatcher) -->
        <?php if (AuthHelper::hasPermission('trips_manage')): ?>
        <li class="menu-item <?php echo $activePage === 'trips' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/trips" class="menu-link">
                <i class="fas fa-route"></i>
                <span>Trips</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Maintenance (Super Admin, Fleet Manager, Safety) -->
        <?php if (AuthHelper::hasPermission('maintenance_manage')): ?>
        <li class="menu-item <?php echo $activePage === 'maintenance' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/maintenance" class="menu-link">
                <i class="fas fa-tools"></i>
                <span>Maintenance</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Fuel (Super Admin, Fleet Manager, Finance) -->
        <?php if (AuthHelper::hasPermission('fuel_manage')): ?>
        <li class="menu-item <?php echo $activePage === 'fuel' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/fuel" class="menu-link">
                <i class="fas fa-gas-pump"></i>
                <span>Fuel Logs</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Expenses (Super Admin, Fleet Manager, Finance) -->
        <?php if (AuthHelper::hasPermission('expenses_manage')): ?>
        <li class="menu-item <?php echo $activePage === 'expenses' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/expenses" class="menu-link">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Expenses</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Reports (Super Admin, Fleet Manager, Finance, Safety) -->
        <?php if (AuthHelper::hasPermission('reports_view') || AuthHelper::hasPermission('reports_manage')): ?>
        <li class="menu-item <?php echo $activePage === 'reports' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/reports" class="menu-link">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>
        <?php endif; ?>

        <!-- Alerts / Notifications -->
        <li class="menu-item <?php echo $activePage === 'notifications' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/notifications" class="menu-link">
                <i class="fas fa-bell"></i>
                <span>System Alerts</span>
            </a>
        </li>

        <!-- Settings & Audit Trails (Super Admin Only) -->
        <?php if (AuthHelper::hasPermission('all')): ?>
        <li class="menu-item <?php echo $activePage === 'settings' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/settings" class="menu-link">
                <i class="fas fa-sliders-h"></i>
                <span>System Settings</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
    
    <div class="sidebar-footer">
        <div class="small">TransitOps System</div>
        <div class="text-light" style="font-size: 0.7rem;">v<?php echo VERSION; ?></div>
    </div>
</aside>
