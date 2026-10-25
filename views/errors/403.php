<?php
if (!defined('SECURE_ACCESS')) exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Access denied | TransitOps</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="error-page">
    <main class="error-card" aria-labelledby="error-title">
        <span class="error-code">403</span>
        <h1 id="error-title">You don't have access to this page</h1>
        <p>Your account may not have permission to view this area. If you think this is a mistake, contact your system administrator.</p>
        <a class="error-action" href="<?php echo BASE_URL; ?>/dashboard">Return to dashboard</a>
    </main>
</body>
</html>
