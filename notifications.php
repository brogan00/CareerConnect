<?php
// notifications.php
define('SECURE_ACCESS', true);
require_once 'connexion/config.php';
require_once 'connexion/check_auth.php';
require_once 'functions/notifications_functions.php';

$title = "Notifications";
require_once 'templates/header.php';

// Mark notifications as read when page loads
if ($_SESSION['user_type'] == "candidat") {
    markNotificationsAsRead($conn, $_SESSION['user_id']);
} elseif ($_SESSION['user_type'] == "admin") {
    markNotificationsAsRead($conn, null, $_SESSION['user_id']);
}

// Get all notifications
if ($_SESSION['user_type'] == "candidat") {
    $notifications = getNotifications($conn, $_SESSION['user_id'], null, 50);
} elseif ($_SESSION['user_type'] == "admin") {
    $notifications = getNotifications($conn, null, $_SESSION['user_id'], 50);
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Your Notifications</h4>
                        <a href="#" class="btn btn-sm btn-light mark-all-read">Mark all as read</a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($notifications)): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <li class="list-group-item <?= $notification['is_read'] ? '' : 'bg-light'; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <a href="<?= getNotificationLink($notification) ?>" class="text-decoration-none">
                                                <p class="mb-1 <?= $notification['is_read'] ? '' : 'fw-bold'; ?>">
                                                    <?= htmlspecialchars($notification['message']); ?>
                                                </p>
                                            </a>
                                            <small class="text-muted">
                                                <?= date("M j, Y g:i a", strtotime($notification['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item mark-as-read" href="#" 
                                                       data-id="<?= $notification['id'] ?>">
                                                        Mark as read
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item delete-notification" href="#" 
                                                       data-id="<?= $notification['id'] ?>">
                                                        Delete
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-bell-slash fs-1 text-muted"></i>
                            <p class="mt-3">No notifications found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Mark all as read
    $('.mark-all-read').click(function(e) {
        e.preventDefault();
        $.post('ajax/mark_all_notifications_read.php', function(response) {
            if (response.success) {
                $('.list-group-item').removeClass('bg-light');
                $('.mb-1').removeClass('fw-bold');
                // Update notification count in header
                $('.notification-count').text('0').addClass('d-none');
            }
        });
    });

    // Mark single notification as read
    $('.mark-as-read').click(function(e) {
        e.preventDefault();
        const notificationId = $(this).data('id');
        $.post('ajax/mark_notification_read.php', {id: notificationId}, function(response) {
            if (response.success) {
                $(this).closest('.list-group-item').removeClass('bg-light');
                $(this).closest('.list-group-item').find('.mb-1').removeClass('fw-bold');
                // Update notification count in header
                const currentCount = parseInt($('.notification-count').text());
                $('.notification-count').text(currentCount - 1);
                if (currentCount - 1 === 0) {
                    $('.notification-count').addClass('d-none');
                }
            }
        });
    });

    // Delete notification
    $('.delete-notification').click(function(e) {
        e.preventDefault();
        const notificationId = $(this).data('id');
        if (confirm('Are you sure you want to delete this notification?')) {
            $.post('ajax/delete_notification.php', {id: notificationId}, function(response) {
                if (response.success) {
                    $(this).closest('.list-group-item').remove();
                    // Update notification count in header if notification was unread
                    if ($(this).closest('.list-group-item').hasClass('bg-light')) {
                        const currentCount = parseInt($('.notification-count').text());
                        $('.notification-count').text(currentCount - 1);
                        if (currentCount - 1 === 0) {
                            $('.notification-count').addClass('d-none');
                        }
                    }
                }
            });
        }
    });
});
</script>

<?php require_once 'templates/footer.php'; ?>