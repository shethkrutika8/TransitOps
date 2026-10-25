<?php
// TransitOps Header Layout
if (!defined('SECURE_ACCESS')) exit;
require_once dirname(dirname(__DIR__)) . '/helpers/AuthHelper.php';
?>
<header class="top-navbar">
    <div class="navbar-left">
        <button id="sidebar-toggle" class="sidebar-toggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="mb-0 fw-bold text-truncate page-shell-title">
            <?php echo htmlspecialchars($headerTitle ?? 'Dashboard', ENT_QUOTES, 'UTF-8'); ?>
        </h4>
    </div>
    
    <div class="navbar-right">
        <!-- Theme Toggle Button -->
        <button id="theme-toggle" class="theme-toggle-btn" type="button" title="Toggle Theme" aria-label="Toggle color theme">
            <i class="fas fa-moon"></i>
        </button>

        <!-- Notifications Dropdown -->
        <div class="dropdown notification-bell">
            <button class="theme-toggle-btn position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                <i class="far fa-bell"></i>
                <span id="notification-badge" class="bell-badge d-none">0</span>
            </button>
            <ul id="notification-list" class="dropdown-menu dropdown-menu-end notification-dropdown shadow border-0 py-0 animate-fade-in" aria-labelledby="notificationDropdown">
                <li class="p-3 text-center text-muted">Loading alerts...</li>
            </ul>
        </div>

        <!-- User Dropdown Menu -->
        <div class="dropdown">
            <button class="user-profile-menu dropdown-toggle" id="userMenuDropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-wrapper">
                    <?php 
                        $name = AuthHelper::userName() ?? 'User';
                        $initials = strtoupper(substr($name, 0, 1));
                        echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8');
                    ?>
                </button>
                <div class="d-none d-md-block text-start">
                    <div class="fw-semibold small" style="line-height: 1.2;"><?php echo htmlspecialchars($name); ?></div>
                    <div class="text-muted text-light" style="font-size: 0.75rem;"><?php echo htmlspecialchars(AuthHelper::userRole()); ?></div>
                </div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end border-0 shadow mt-2 animate-fade-in" aria-labelledby="userMenuDropdown">
                <li class="px-3 py-2 border-bottom d-md-none">
                    <div class="fw-bold"><?php echo htmlspecialchars($name); ?></div>
                    <div class="small text-muted"><?php echo htmlspecialchars(AuthHelper::userRole()); ?></div>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?php echo BASE_URL; ?>/settings">
                        <i class="fas fa-cog text-muted me-2"></i> Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" href="<?php echo BASE_URL; ?>/logout">
                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>
