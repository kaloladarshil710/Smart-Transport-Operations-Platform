<?php
/**
 * Shared page header.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(APP_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/style.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/dashboard.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/sidebar.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/navbar.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/cards.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/forms.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/tables.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/animations.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/responsive.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/dark-theme.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/utilities.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/icons/font-awesome.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/icons/material-icons.css')); ?>">
</head>
<body>
<?php if ($flash): ?>
<div class="flash-message flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
<?php endif; ?>
