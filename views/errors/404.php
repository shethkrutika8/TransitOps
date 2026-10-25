<?php
if (!defined('SECURE_ACCESS')) exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Page not found | TransitOps</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="error-page">
    <main class="error-card" aria-labelledby="error-title">
        <span class="error-code">404</span>
        <h1 id="error-title">We couldn't find that page</h1>
        <p>The address may be incorrect, or the page may have moved. Use the button below to return to your fleet dashboard.</p>
        <a class="error-action" href="<?php echo BASE_URL; ?>/dashboard">Return to dashboard</a>
    </main>
</body>
</html>
