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
    <title><?php echo e(($pageTitle ?? APP_NAME) . ' | ' . APP_NAME); ?></title>
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/variables.css')); ?>">
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
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/alerts.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/buttons.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/badges.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/modals.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/login.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/profile.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/settings.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/reports.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/trip.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/driver.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/driver-table.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/driver-form.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/driver-profile.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/fuel.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/fuel-table.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/fuel-form.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/expense.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/expense-table.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/css/expense-form.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/icons/font-awesome.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(siteUrl('assets/icons/material-icons.css')); ?>">
</head>
<body>
<?php require __DIR__ . '/loader.php'; ?>
<?php if ($flash): ?>
<div class="flash-message flash-<?php echo e($flash['type']); ?>"><?php echo e($flash['message']); ?></div>
<?php endif; ?>
