<?php
// includes/template.php - Template responsive
include 'config.php';
include 'auth.php';
requireLogin();

$page_title = isset($page_title) ? $page_title : APP_NAME;
?>
<!DOCTYPE html>
<html lang="fr" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="Système de gestion BRALIMA MegaData">
    <title><?php echo $page_title; ?></title>

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?php echo $extra_css; ?>">
    <?php endif; ?>

    <!-- Meta pour mobile -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content" id="main-content">
            <?php if (isset($page_header)): ?>
                <div class="page-header">
                    <h1><?php echo $page_header['title']; ?></h1>
                    <?php if (isset($page_header['description'])): ?>
                        <p><?php echo $page_header['description']; ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Contenu dynamique -->
            <?php echo $content ?? ''; ?>
        </main>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/responsive.js"></script>
    <?php if (isset($extra_js)): ?>
        <script src="<?php echo $extra_js; ?>"></script>
    <?php endif; ?>

    <?php if (isset($scripts)): ?>
        <?php echo $scripts; ?>
    <?php endif; ?>
</body>

</html>