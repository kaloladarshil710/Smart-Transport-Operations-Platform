<?php
/**
 * Shared page footer.
 */
declare(strict_types=1);
?>
<script src="<?php echo e(siteUrl('assets/js/main.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/dashboard.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/validation.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/ajax.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/charts.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/sidebar.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/modal.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/theme.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/notifications.js')); ?>"></script>
<script src="<?php echo e(siteUrl('assets/js/toast.js')); ?>"></script>
<?php require __DIR__ . '/modal.php'; ?>
<?php require __DIR__ . '/../components/toast.php'; ?>
</body>
</html>
