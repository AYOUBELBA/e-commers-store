<?php
$pageTitle = 'Page Not Found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found - <?php echo APP_NAME ?? 'ModernStore'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="text-center">
            <div class="text-9xl font-bold text-blue-600 mb-4">404</div>
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white mb-4">Page Not Found</h1>
            <p class="text-gray-600 dark:text-gray-400 mb-8">The page you are looking for doesn't exist or has been moved.</p>
            <a href="<?php echo BASE_URL ?? '/store-projet/public'; ?>" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="ri-home-line mr-2"></i> Back to Home
            </a>
        </div>
    </div>
</body>
</html>