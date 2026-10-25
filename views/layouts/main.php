<?php
// TransitOps Main Shell Layout
if (!defined('SECURE_ACCESS')) exit;
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'TransitOps - Smart Transport Operations Management System', ENT_QUOTES, 'UTF-8'); ?></title>
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="TransitOps is a centralized Transport Operations Management System for logistics companies. Control vehicle fleets, driver records, expenses, fuels, maintenance schedules, and active trips.">
    <meta name="author" content="TransitOps Development Team">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0f172a">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- DataTables & Plugins CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
    
    <!-- Custom Application CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>

    <a class="skip-link" href="#main-content">Skip to content</a>
    <div id="app-container">
        <!-- Sidebar Navigation -->
        <?php include __DIR__ . '/sidebar.php'; ?>
        
        <!-- Main Content Area -->
        <main id="main-content" tabindex="-1">
            <!-- Top Navigation Navbar -->
            <?php include __DIR__ . '/header.php'; ?>
            
            <!-- Dynamic Content Viewport -->
            <div class="content-body animate-fade-in">
                <?php include $contentView; ?>
            </div>
        </main>
    </div>

    <!-- Footer Layout & Script Imports -->
    <?php include __DIR__ . '/footer.php'; ?>
