/**
 * TransitOps Enterprise Dashboard
 * Interactive features: live clock, KPI refresh, counter animation,
 * notification management, activity feed, trip updates.
 *
 * @package TransitOps
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var $ = function (sel, ctx) { return (ctx || document).querySelector(sel); };
    var $$ = function (sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); };

    var timeEl = document.getElementById('current-time');
    var dateEl = document.getElementById('current-date');
    var unreadBadge = document.getElementById('unread-count');
    var vehicleStatusList = document.getElementById('vehicle-status-list');
    var driverStatusList = document.getElementById('driver-status-list');
    var tripsBody = document.getElementById('recent-trips-body');
    var activityTimeline = document.getElementById('activity-timeline');
    var notificationList = document.getElementById('notification-list');
    var loaderEl = document.getElementById('page-loader');

    var dashboardData = window.__DASHBOARD_DATA__ || {};
    var refreshInterval = null;

    /* ─── Live Clock ─── */
    function updateClock() {
        var now = new Date();
        if (timeEl) {
            var h = String(now.getHours()).padStart(2, '0');
            var m = String(now.getMinutes()).padStart(2, '0');
            var s = String(now.getSeconds()).padStart(2, '0');
            timeEl.textContent = h + ':' + m + ':' + s;
        }
        if (dateEl) {
            var opts = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            dateEl.textContent = now.toLocaleDateString('en-IN', opts);
        }
    }
    updateClock();
    setInterval(updateClock, 1000);

    /* ─── Counter Animation ─── */
    function animateCounter(el, target, suffix) {
        suffix = suffix || '';
        var duration = 1200;
        var steps = 30;
        var stepTime = duration / steps;
        var current = 0;
        var increment = target / steps;
        var timer = setInterval(function () {
            current += increment;
            if (current >= target) { current = target; clearInterval(timer); }
            if (Number.isInteger(target)) {
                el.textContent = Math.round(current).toLocaleString() + suffix;
            } else {
                el.textContent = current.toFixed(2) + suffix;
            }
        }, stepTime);
    }

    function runCounterAnimations() {
        if (!dashboardData.kpi) return;
        var kpi = dashboardData.kpi;
        var map = {
            'kpi-total-vehicles': kpi.total_vehicles,
            'kpi-available-vehicles': kpi.available_vehicles,
            'kpi-vehicles-on-trip': kpi.vehicles_on_trip,
            'kpi-maintenance': kpi.vehicles_in_maintenance,
            'kpi-retired': kpi.retired_vehicles,
            'kpi-total-drivers': kpi.total_drivers,
            'kpi-available-drivers': kpi.available_drivers,
            'kpi-drivers-trip': kpi.drivers_on_trip,
            'kpi-suspended': kpi.suspended_drivers,
            'kpi-trips-today': kpi.trips_today,
            'kpi-active-trips': kpi.active_trips,
            'kpi-completed-trips': kpi.completed_trips,
            'kpi-cancelled-trips': kpi.cancelled_trips
        };
        Object.keys(map).forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            animateCounter(el, map[id], '');
        });
    }
    runCounterAnimations();

    /* ─── Stagger Animations ─── */
    $$('.kpi-card').forEach(function (card, i) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        setTimeout(function () {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 80 * i);
    });

    $$('.status-item').forEach(function (item, i) {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-10px)';
        item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        setTimeout(function () {
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, 60 * i);
    });

    $$('.chart-card').forEach(function (card, i) {
        card.style.opacity = '0';
        card.style.transform = 'translateY(15px)';
        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        setTimeout(function () {
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * i);
    });

    /* ─── AJAX Data Refresh ─── */
    function refreshDashboardData(silent) {
        silent = silent || false;
        if (!silent && loaderEl) loaderEl.removeAttribute('hidden');

        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'ajax/dashboard.php?action=all', true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            if (loaderEl) loaderEl.setAttribute('hidden', '');
            if (xhr.status !== 200) return;
            try {
                var resp = JSON.parse(xhr.responseText);
                if (resp.success && resp.data) updateDashboardData(resp.data);
            } catch (e) { /* silent */ }
        };
        xhr.onerror = function () {
            if (loaderEl) loaderEl.setAttribute('hidden', '');
        };
        xhr.send();
    }

    /* ─── Update Dashboard DOM ─── */
    function updateDashboardData(data) {
        if (!data) return;

        /* Update KPI values */
        if (data.kpi) {
            Object.keys(data.kpi).forEach(function (key) {
                var el = document.getElementById('kpi-' + key.replace(/_/g, '-'));
                if (!el) return;
                var val = data.kpi[key];
                if (key.indexOf('cost') > -1 || key === 'total_revenue' || key === 'operational_cost') {
                    el.textContent = '\u20B9' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                } else if (key === 'fleet_utilization' || key === 'vehicle_roi') {
                    el.textContent = val + '%';
                } else if (key === 'fuel_efficiency') {
                    el.textContent = val + ' km/L';
                } else {
                    el.textContent = Number(val).toLocaleString();
                }
            });
        }

        /* Vehicle status */
        if (data.vehicle_status && vehicleStatusList) {
            vehicleStatusList.innerHTML = data.vehicle_status.map(function (vs) {
                return '<div class="status-item"><span class="status-indicator" style="background:' + vs.color + '"></span><i class="fa ' + vs.icon + '" style="color:' + vs.color + '"></i><span class="status-label">' + vs.label + '</span><span class="status-count">' + vs.count + '</span></div>';
            }).join('');
        }

        /* Driver status */
        if (data.driver_status && driverStatusList) {
            driverStatusList.innerHTML = data.driver_status.map(function (ds) {
                return '<div class="status-item"><span class="status-indicator" style="background:' + ds.color + '"></span><i class="fa ' + ds.icon + '" style="color:' + ds.color + '"></i><span class="status-label">' + ds.label + '</span><span class="status-count">' + ds.count + '</span></div>';
            }).join('');
        }

        /* Recent trips */
        if (data.recent_trips && tripsBody) {
            if (!data.recent_trips.length) {
                tripsBody.innerHTML = '<tr><td colspan="7" class="empty-state">No trips recorded yet.</td></tr>';
            } else {
                tripsBody.innerHTML = data.recent_trips.map(function (t) {
                    var bc = 'badge-secondary';
                    if (t.status === 'Completed') bc = 'badge-success';
                    else if (t.status === 'Cancelled') bc = 'badge-danger';
                    else if (t.status === 'Dispatched') bc = 'badge-info';
                    return '<tr><td><a href="modules/trips/index.php?id=' + t.id + '" class="trip-link">' + esc(t.trip_number) + '</a></td><td>' + esc(t.vehicle) + '</td><td>' + esc(t.driver) + '</td><td><span class="route-text">' + esc(t.origin) + ' \u2192 ' + esc(t.destination) + '</span></td><td><span class="badge ' + bc + '">' + esc(t.status) + '</span></td><td>' + esc(t.dispatch_time) + '</td><td><a href="modules/trips/index.php?id=' + t.id + '" class="btn btn-sm btn-outline"><i class="fa fa-eye"></i></a></td></tr>';
                }).join('');
            }
        }

        /* Activities */
        if (data.activities && activityTimeline) {
            if (!data.activities.length) {
                activityTimeline.innerHTML = '<div class="empty-state">No recent activities.</div>';
            } else {
                activityTimeline.innerHTML = data.activities.map(function (a) {
                    return '<div class="activity-item"><div class="activity-dot"></div><div class="activity-content"><p class="activity-action">' + esc(a.action) + '</p><p class="activity-desc">' + esc(a.description || '') + '</p><span class="activity-meta"><span class="activity-user"><i class="fa fa-user"></i> ' + esc(a.user) + '</span><span class="activity-time"><i class="fa fa-clock-o"></i> ' + esc(a.time) + '</span></span></div></div>';
                }).join('');
            }
        }

        /* Notifications */
        if (data.notifications && notificationList) {
            if (!data.notifications.length) {
                notificationList.innerHTML = '<div class="empty-state">No notifications.</div>';
            } else {
                notificationList.innerHTML = data.notifications.map(function (n) {
                    var ic = 'fa-bell text-muted';
                    if (n.priority === 'High') ic = 'fa-exclamation-circle text-danger';
                    else if (n.priority === 'Medium') ic = 'fa-info-circle text-warning';
                    var pb = '';
                    if (n.priority === 'High') pb = '<span class="badge badge-danger">High Priority</span>';
                    else if (n.priority === 'Medium') pb = '<span class="badge badge-warning">Medium</span>';
                    var msg = n.message.length > 80 ? n.message.substring(0, 80) + '...' : n.message;
                    var markBtn = n.is_read ? '' : '<button class="notification-mark-read" onclick="markNotificationRead(' + n.id + ')" title="Mark as read"><i class="fa fa-check"></i></button>';
                    return '<div class="notification-item' + (n.is_read ? '' : ' notification-unread') + '" data-id="' + n.id + '"><div class="notification-icon"><i class="fa ' + ic + '"></i></div><div class="notification-content"><p class="notification-title">' + esc(n.title) + '</p><p class="notification-message">' + esc(msg) + '</p><span class="notification-time">' + esc(n.time) + '</span> ' + pb + '</div>' + markBtn + '</div>';
                }).join('');
            }
        }

        /* Unread count */
        if (typeof data.unread_notifications !== 'undefined' && unreadBadge) {
            unreadBadge.textContent = data.unread_notifications;
        }
    }

    /* ─── Helpers ─── */
    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    /* ─── Auto-refresh every 30s ─── */
    function startAutoRefresh() {
        if (refreshInterval) clearInterval(refreshInterval);
        refreshInterval = setInterval(function () { refreshDashboardData(true); }, 30000);
    }
    startAutoRefresh();

    /* ─── Manual refresh ─── */
    window.refreshDashboard = function () { refreshDashboardData(false); };

    /* ─── Mark notification read ─── */
    window.markNotificationRead = function (id) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'ajax/notifications.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onload = function () {
            if (xhr.status === 200) {
                var item = document.querySelector('.notification-item[data-id="' + id + '"]');
                if (item) {
                    item.classList.remove('notification-unread');
                    var btn = item.querySelector('.notification-mark-read');
                    if (btn) btn.remove();
                }
                refreshDashboardData(true);
            }
        };
        xhr.send('action=mark_read&id=' + id);
    };

    /* ─── Refresh charts ─── */
    window.refreshCharts = function () {
        refreshDashboardData(true);
    };

    /* ─── Tab visibility refresh ─── */
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) refreshDashboardData(true);
    });
});
