/**
 * TransitOps Enterprise Dashboard
 * Chart.js configurations for all 12 dashboard charts:
 * Bar, Line, Area, Doughnut, Pie, Horizontal Bar
 *
 * @package TransitOps
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    var data = window.__DASHBOARD_DATA__ || {};

    /* ─── Chart Defaults ─── */
    Chart.defaults.font.family = 'Inter, Poppins, Roboto, sans-serif';
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.legend.labels.padding = 16;

    var isDark = document.body.classList.contains('dark');
    var gridColor = isDark ? 'rgba(148,163,184,0.08)' : 'rgba(148,163,184,0.15)';
    var textColor = isDark ? '#94a3b8' : '#64748b';

    /* ─── Color Palette ─── */
    var colors = {
        primary: '#2563eb',
        success: '#22c55e',
        danger: '#ef4444',
        warning: '#f59e0b',
        info: '#0ea5e9',
        purple: '#8b5cf6',
        pink: '#ec4899',
        orange: '#f97316',
        teal: '#14b8a6',
        gray: '#64748b'
    };

    var chartColors = [
        '#2563eb', '#22c55e', '#f59e0b', '#ef4444', '#8b5cf6',
        '#0ea5e9', '#ec4899', '#f97316', '#14b8a6', '#64748b'
    ];

    /* ─── Utility: Gradient Fill ─── */
    function createGradient(ctx, chartArea, color, alphaTop, alphaBottom) {
        alphaTop = typeof alphaTop !== 'undefined' ? alphaTop : 0.3;
        alphaBottom = typeof alphaBottom !== 'undefined' ? alphaBottom : 0.0;
        var grad = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        grad.addColorStop(0, hexToRgba(color, alphaTop));
        grad.addColorStop(1, hexToRgba(color, alphaBottom));
        return grad;
    }

    function hexToRgba(hex, alpha) {
        var r = parseInt(hex.slice(1, 3), 16);
        var g = parseInt(hex.slice(3, 5), 16);
        var b = parseInt(hex.slice(5, 7), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    /* ─── Shared Chart Options ─── */
    function baseOptions(extra) {
        extra = extra || {};
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 14, boxWidth: 8, font: { size: 11 } }
                },
                tooltip: {
                    backgroundColor: isDark ? '#1e293b' : '#fff',
                    titleColor: isDark ? '#e2e8f0' : '#0f172a',
                    bodyColor: isDark ? '#94a3b8' : '#64748b',
                    borderColor: isDark ? '#263449' : '#e2e8f0',
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    boxPadding: 4,
                    usePointStyle: true
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: textColor, font: { size: 10 } }
                },
                y: {
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { color: textColor, font: { size: 10 }, callback: function (val) { if (Math.abs(val) >= 1000) return val / 1000 + 'k'; return val; } }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        };
    }

    /* ─── Helper: Fetch canvas context ─── */
    function getCtx(id) {
        var canvas = document.getElementById(id);
        if (!canvas) return null;
        return canvas.getContext('2d');
    }

    /* ─── 1. Monthly Trips (Stacked Bar) ─── */
    (function () {
        var ctx = getCtx('chart-monthly-trips');
        if (!ctx) return;
        var trips = data.chart_monthly_trips || [];
        var labels = trips.map(function (t) { return t.month_display; });
        var completed = trips.map(function (t) { return t.completed; });
        var cancelled = trips.map(function (t) { return t.cancelled; });
        var active = trips.map(function (t) { return t.active; });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    { label: 'Completed', data: completed.length ? completed : [0, 0, 0, 0, 0, 0], backgroundColor: colors.success, borderRadius: 4 },
                    { label: 'Active', data: active.length ? active : [0, 0, 0, 0, 0, 0], backgroundColor: colors.primary, borderRadius: 4 },
                    { label: 'Cancelled', data: cancelled.length ? cancelled : [0, 0, 0, 0, 0, 0], backgroundColor: colors.danger, borderRadius: 4 }
                ]
            },
            options: Object.assign(baseOptions(), {
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, boxWidth: 8 } }
                },
                scales: {
                    x: { stacked: true, grid: { display: false }, ticks: { color: textColor } },
                    y: { stacked: true, grid: { color: gridColor }, ticks: { color: textColor } }
                }
            })
        });
    })();

    /* ─── 2. Revenue vs Expense (Line) ─── */
    (function () {
        var ctx = getCtx('chart-revenue-expense');
        if (!ctx) return;
        var rve = data.chart_revenue_vs_expense || [];
        var labels = rve.map(function (d) { return d.month_display; });
        var rev = rve.map(function (d) { return d.revenue; });
        var exp = rve.map(function (d) { return d.total_expense; });
        var profit = rve.map(function (d) { return d.profit; });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    { label: 'Revenue', data: rev.length ? rev : [0, 0, 0, 0, 0, 0], borderColor: colors.success, backgroundColor: function (context) { var c = context.chart; if (!c.chartArea) return 'rgba(34,197,94,0.1)'; return createGradient(c.ctx, c.chartArea, colors.success, 0.15, 0); }, fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: colors.success, borderWidth: 3 },
                    { label: 'Expenses', data: exp.length ? exp : [0, 0, 0, 0, 0, 0], borderColor: colors.danger, backgroundColor: function (context) { var c = context.chart; if (!c.chartArea) return 'rgba(239,68,68,0.1)'; return createGradient(c.ctx, c.chartArea, colors.danger, 0.15, 0); }, fill: true, tension: 0.4, pointRadius: 4, pointBackgroundColor: colors.danger, borderWidth: 3 },
                    { label: 'Profit', data: profit.length ? profit : [0, 0, 0, 0, 0, 0], borderColor: colors.primary, backgroundColor: function (context) { var c = context.chart; if (!c.chartArea) return 'rgba(37,99,235,0.1)'; return createGradient(c.ctx, c.chartArea, colors.primary, 0.1, 0); }, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: colors.primary, borderWidth: 2, borderDash: [5, 5] }
                ]
            },
            options: Object.assign(baseOptions(), {
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, boxWidth: 8 } },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.dataset.label + ': \u20B9' + Number(ctx.raw).toLocaleString('en-IN'); } } }
                }
            })
        });
    })();

    /* ─── 3. Vehicle Status (Doughnut) ─── */
    (function () {
        var ctx = getCtx('chart-vehicle-status');
        if (!ctx) return;
        var vs = data.vehicleStatus || [];
        var labels = vs.map(function (v) { return v.label; });
        var counts = vs.map(function (v) { return v.count; });
        var bgColors = vs.map(function (v) { return v.color; });

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels.length ? labels : ['Available', 'On Trip', 'Maintenance', 'Retired'],
                datasets: [{
                    data: counts.length ? counts : [0, 0, 0, 0],
                    backgroundColor: bgColors.length ? bgColors : ['#22c55e', '#2563eb', '#f59e0b', '#64748b'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, boxWidth: 8, font: { size: 11 } } },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#fff',
                        titleColor: isDark ? '#e2e8f0' : '#0f172a',
                        bodyColor: isDark ? '#94a3b8' : '#64748b',
                        borderColor: isDark ? '#263449' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    })();

    /* ─── 4. Driver Status (Doughnut) ─── */
    (function () {
        var ctx = getCtx('chart-driver-status');
        if (!ctx) return;
        var ds = data.driverStatus || [];
        var labels = ds.map(function (d) { return d.label; });
        var counts = ds.map(function (d) { return d.count; });
        var bgColors = ds.map(function (d) { return d.color; });

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels.length ? labels : ['Available', 'On Trip', 'Off Duty', 'Suspended'],
                datasets: [{
                    data: counts.length ? counts : [0, 0, 0, 0],
                    backgroundColor: bgColors.length ? bgColors : ['#22c55e', '#2563eb', '#8b5cf6', '#ef4444'],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, boxWidth: 8, font: { size: 11 } } },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#fff',
                        titleColor: isDark ? '#e2e8f0' : '#0f172a',
                        bodyColor: isDark ? '#94a3b8' : '#64748b',
                        borderColor: isDark ? '#263449' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    })();

    /* ─── 5. Monthly Revenue (Area) ─── */
    (function () {
        var ctx = getCtx('chart-monthly-revenue');
        if (!ctx) return;
        var rev = data.chart_monthly_revenue || [];
        var labels = rev.map(function (r) { return r.month_display; });
        var values = rev.map(function (r) { return r.revenue; });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: values.length ? values : [0, 0, 0, 0, 0, 0],
                    borderColor: colors.success,
                    backgroundColor: function (context) { var c = context.chart; if (!c.chartArea) return 'rgba(34,197,94,0.15)'; return createGradient(c.ctx, c.chartArea, colors.success, 0.25, 0); },
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: colors.success,
                    borderWidth: 3
                }]
            },
            options: Object.assign(baseOptions(), {
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return '\u20B9' + Number(ctx.raw).toLocaleString('en-IN'); } } }
                },
                scales: {
                    y: { grid: { color: gridColor }, ticks: { color: textColor, callback: function (val) { return '\u20B9' + (val / 1000).toFixed(0) + 'k'; } } }
                }
            })
        });
    })();

    /* ─── 6. Monthly Expenses (Area) ─── */
    (function () {
        var ctx = getCtx('chart-monthly-expenses');
        if (!ctx) return;
        var exp = data.chart_monthly_expenses || [];
        var labels = exp.map(function (e) { return e.month_display; });
        var values = exp.map(function (e) { return e.total_expense; });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Expenses',
                    data: values.length ? values : [0, 0, 0, 0, 0, 0],
                    borderColor: colors.danger,
                    backgroundColor: function (context) { var c = context.chart; if (!c.chartArea) return 'rgba(239,68,68,0.15)'; return createGradient(c.ctx, c.chartArea, colors.danger, 0.25, 0); },
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: colors.danger,
                    borderWidth: 3
                }]
            },
            options: Object.assign(baseOptions(), {
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return '\u20B9' + Number(ctx.raw).toLocaleString('en-IN'); } } }
                },
                scales: {
                    y: { grid: { color: gridColor }, ticks: { color: textColor, callback: function (val) { return '\u20B9' + (val / 1000).toFixed(0) + 'k'; } } }
                }
            })
        });
    })();

    /* ─── 7. Fuel Consumption (Bar) ─── */
    (function () {
        var ctx = getCtx('chart-fuel-consumption');
        if (!ctx) return;
        var fuel = data.chart_monthly_fuel || [];
        var labels = fuel.map(function (f) { return f.month_display; });
        var liters = fuel.map(function (f) { return f.total_liters; });
        var costs = fuel.map(function (f) { return f.total_cost; });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [
                    { label: 'Liters', data: liters.length ? liters : [0, 0, 0, 0, 0, 0], backgroundColor: colors.warning, borderRadius: 4, order: 2 },
                    { label: 'Cost (\u20B9)', data: costs.length ? costs : [0, 0, 0, 0, 0, 0], backgroundColor: colors.primary, borderRadius: 4, order: 1, yAxisID: 'y1' }
                ]
            },
            options: Object.assign(baseOptions(), {
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, boxWidth: 8 } }
                },
                scales: {
                    y: { position: 'left', grid: { color: gridColor }, ticks: { color: textColor, callback: function (val) { return val + 'L'; } } },
                    y1: { position: 'right', grid: { display: false }, ticks: { color: textColor, callback: function (val) { return '\u20B9' + (val / 1000).toFixed(0) + 'k'; } } }
                }
            })
        });
    })();

    /* ─── 8. Maintenance Cost (Bar) ─── */
    (function () {
        var ctx = getCtx('chart-maintenance-cost');
        if (!ctx) return;
        var maint = data.chart_monthly_maintenance || [];
        var labels = maint.map(function (m) { return m.month_display; });
        var values = maint.map(function (m) { return m.total_cost; });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Maintenance Cost',
                    data: values.length ? values : [0, 0, 0, 0, 0, 0],
                    backgroundColor: function (context) {
                        var chartColors = ['#f59e0b', '#f97316', '#ef4444', '#8b5cf6', '#0ea5e9', '#14b8a6'];
                        return chartColors[context.dataIndex % chartColors.length];
                    },
                    borderRadius: 4
                }]
            },
            options: Object.assign(baseOptions(), {
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return '\u20B9' + Number(ctx.raw).toLocaleString('en-IN'); } } }
                },
                scales: {
                    y: { ticks: { color: textColor, callback: function (val) { return '\u20B9' + (val / 1000).toFixed(0) + 'k'; } } }
                }
            })
        });
    })();

    /* ─── 9. Fleet Utilization Trend (Line) ─── */
    (function () {
        var ctx = getCtx('chart-fleet-utilization');
        if (!ctx) return;
        var util = data.chart_fleet_utilization || [];
        var labels = util.map(function (u) { return u.month_display; });
        var values = util.map(function (u) { return u.utilization_percent; });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Utilization %',
                    data: values.length ? values : [0, 0, 0, 0, 0, 0],
                    borderColor: colors.purple,
                    backgroundColor: function (context) { var c = context.chart; if (!c.chartArea) return 'rgba(139,92,246,0.15)'; return createGradient(c.ctx, c.chartArea, colors.purple, 0.2, 0); },
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointBackgroundColor: colors.purple,
                    borderWidth: 3,
                    pointHoverRadius: 7
                }]
            },
            options: Object.assign(baseOptions(), {
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.raw + '%'; } } }
                },
                scales: {
                    y: { min: 0, max: 100, grid: { color: gridColor }, ticks: { color: textColor, callback: function (val) { return val + '%'; } } }
                }
            })
        });
    })();

    /* ─── 10. Top Vehicles (Horizontal Bar) ─── */
    (function () {
        var ctx = getCtx('chart-top-vehicles');
        if (!ctx) return;
        var vehicles = data.chart_top_vehicles || [];
        var labels = vehicles.map(function (v) { return v.vehicle_name || v.registration_number; });
        var revs = vehicles.map(function (v) { return v.total_revenue; });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels.reverse() : ['No data'],
                datasets: [{
                    label: 'Revenue',
                    data: revs.length ? revs.reverse() : [0],
                    backgroundColor: function (context) {
                        var clrs = ['#2563eb', '#22c55e', '#f59e0b', '#8b5cf6', '#0ea5e9', '#ec4899', '#f97316', '#14b8a6', '#64748b', '#ef4444'];
                        return clrs[context.dataIndex % clrs.length];
                    },
                    borderRadius: 4
                }]
            },
            options: Object.assign(baseOptions(), {
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return '\u20B9' + Number(ctx.raw).toLocaleString('en-IN'); } } }
                },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor, callback: function (val) { return '\u20B9' + (val / 1000).toFixed(0) + 'k'; } } },
                    y: { grid: { display: false }, ticks: { color: textColor, font: { size: 10 } } }
                }
            })
        });
    })();

    /* ─── 11. Top Drivers (Horizontal Bar) ─── */
    (function () {
        var ctx = getCtx('chart-top-drivers');
        if (!ctx) return;
        var drivers = data.chart_top_drivers || [];
        var labels = drivers.map(function (d) { return d.full_name; });
        var revs = drivers.map(function (d) { return d.total_revenue; });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.length ? labels.reverse() : ['No data'],
                datasets: [{
                    label: 'Revenue',
                    data: revs.length ? revs.reverse() : [0],
                    backgroundColor: function (context) {
                        var clrs = ['#8b5cf6', '#2563eb', '#22c55e', '#f59e0b', '#0ea5e9', '#ec4899', '#f97316', '#14b8a6', '#64748b', '#ef4444'];
                        return clrs[context.dataIndex % clrs.length];
                    },
                    borderRadius: 4
                }]
            },
            options: Object.assign(baseOptions(), {
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return '\u20B9' + Number(ctx.raw).toLocaleString('en-IN'); } } }
                },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor, callback: function (val) { return '\u20B9' + (val / 1000).toFixed(0) + 'k'; } } },
                    y: { grid: { display: false }, ticks: { color: textColor, font: { size: 10 } } }
                }
            })
        });
    })();

    /* ─── 12. Revenue vs Cost Overview (Pie) ─── */
    (function () {
        var ctx = getCtx('chart-roi');
        if (!ctx) return;
        var kpi = data.kpi || {};
        var revenue = parseFloat(kpi.total_revenue) || 0;
        var fuelCost = parseFloat(kpi.fuel_cost) || 0;
        var maintCost = parseFloat(kpi.maintenance_cost) || 0;
        var opCost = parseFloat(kpi.operational_cost) || 0;
        var totalCost = fuelCost + maintCost + opCost;

        var chartData = {
            labels: ['Revenue', 'Fuel Cost', 'Maintenance', 'Operational'],
            datasets: [{
                data: [revenue || 1, fuelCost || 1, maintCost || 1, opCost || 1],
                backgroundColor: ['#22c55e', '#f59e0b', '#0ea5e9', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        };

        // If all zeros, show equal slices for demo
        if (revenue === 0 && fuelCost === 0 && maintCost === 0 && opCost === 0) {
            chartData.datasets[0].data = [25, 25, 25, 25];
        }

        new Chart(ctx, {
            type: 'pie',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { usePointStyle: true, padding: 12, boxWidth: 8, font: { size: 11 } }
                    },
                    tooltip: {
                        backgroundColor: isDark ? '#1e293b' : '#fff',
                        titleColor: isDark ? '#e2e8f0' : '#0f172a',
                        bodyColor: isDark ? '#94a3b8' : '#64748b',
                        borderColor: isDark ? '#263449' : '#e2e8f0',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (ctx) {
                                var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                var pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ctx.label + ': \u20B9' + Number(ctx.raw).toLocaleString('en-IN') + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    })();

    /* ─── Dark Mode Observer ─── */
    var themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            setTimeout(function () {
                /* Simple page reload on theme toggle to redraw charts with new colors */
                location.reload();
            }, 100);
        });
    }

    /* Mark charts as initialized */
    window.__CHARTS_INITIALIZED__ = true;
});
