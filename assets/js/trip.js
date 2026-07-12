/**
 * TransitOps Trip Management
 * Interactive features for trip list, create, edit, and view pages.
 *
 * @package TransitOps
 */

document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    /* ─── AJAX Trip Search (List Page) ─── */
    var searchInput = document.getElementById('filter-search');
    var searchTimer = null;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                document.getElementById('trip-filter-form').submit();
            }, 600);
        });
    }

    /* ─── Vehicle/Driver Selection Info (Create/Edit Page) ─── */
    var vehicleSelect = document.getElementById('vehicle_id');
    var vehicleInfo = document.getElementById('vehicle-info');
    var driverSelect = document.getElementById('driver_id');
    var driverInfo = document.getElementById('driver-info');
    var cargoInput = document.getElementById('cargo_weight_kg');
    var cargoWarning = document.getElementById('cargo-warning');
    var estimatedFuel = document.getElementById('estimated_fuel');
    var distanceInput = document.getElementById('planned_distance_km');

    if (vehicleSelect && vehicleInfo) {
        vehicleSelect.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (!opt || !opt.value) {
                vehicleInfo.style.display = 'none';
                return;
            }
            document.getElementById('v-capacity').textContent = opt.dataset.capacity || '—';
            document.getElementById('v-fuel').textContent = opt.dataset.fuelType || '—';
            document.getElementById('v-status').textContent = opt.dataset.status || '—';
            document.getElementById('v-eff').textContent = opt.dataset.fuelEff || '—';
            vehicleInfo.style.display = 'block';
            if (cargoInput) validateCargoWeight();
            if (distanceInput) estimateFuel();
        });
    }

    if (driverSelect && driverInfo) {
        driverSelect.addEventListener('change', function () {
            var opt = this.options[this.selectedIndex];
            if (!opt || !opt.value) {
                driverInfo.style.display = 'none';
                return;
            }
            document.getElementById('d-license').textContent = opt.dataset.license || '—';
            document.getElementById('d-expiry').textContent = opt.dataset.licenseExpiry || '—';
            document.getElementById('d-status').textContent = opt.dataset.status || '—';
            document.getElementById('d-safety').textContent = opt.dataset.safety || '0';
            driverInfo.style.display = 'block';
        });
    }

    function validateCargoWeight() {
        if (!cargoInput || !vehicleSelect || !cargoWarning) return;
        var opt = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (!opt || !opt.value) { cargoWarning.style.display = 'none'; return; }
        var capacity = parseInt(opt.dataset.capacity) || 0;
        var weight = parseInt(cargoInput.value) || 0;
        cargoWarning.style.display = (weight > 0 && capacity > 0 && weight > capacity) ? 'block' : 'none';
    }

    if (cargoInput) cargoInput.addEventListener('input', validateCargoWeight);

    function estimateFuel() {
        if (!distanceInput || !vehicleSelect || !estimatedFuel) return;
        var opt = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (!opt || !opt.value) return;
        var eff = parseFloat(opt.dataset.fuelEff) || 8.5;
        var dist = parseFloat(distanceInput.value) || 0;
        if (dist > 0 && eff > 0) {
            estimatedFuel.value = (dist / eff).toFixed(1);
        }
    }

    if (distanceInput) distanceInput.addEventListener('input', estimateFuel);

    /* ─── Dynamic Vehicle Search (Filter) ─── */
    var suggestSearch = document.querySelector('[data-trip-suggest]');
    if (suggestSearch) {
        suggestSearch.addEventListener('input', function () {
            var q = this.value.trim();
            if (q.length < 2) return;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'ajax/search.php?suggest=true&q=' + encodeURIComponent(q), true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function () {
                if (xhr.status === 200) {
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.success && resp.data) {
                            showSuggestions(resp.data);
                        }
                    } catch (e) {}
                }
            };
            xhr.send();
        });
    }

    function showSuggestions(results) {
        var container = document.getElementById('suggestions-container');
        if (!container) return;
        if (!results.length) {
            container.innerHTML = '';
            container.style.display = 'none';
            return;
        }
        container.innerHTML = results.map(function (t) {
            return '<div class="suggestion-item" data-id="' + t.id + '">' +
                '<strong>' + esc(t.trip_number) + '</strong> — ' +
                esc(t.registration_number) + ' — ' +
                esc(t.origin) + ' → ' + esc(t.destination) +
                ' <span class="badge badge-sm badge-' + getStatusBadge(t.status) + '">' + esc(t.status) + '</span>' +
                '</div>';
        }).join('');
        container.style.display = 'block';

        container.querySelectorAll('.suggestion-item').forEach(function (item) {
            item.addEventListener('click', function () {
                window.location.href = 'modules/trips/view.php?id=' + this.dataset.id;
            });
        });
    }

    function getStatusBadge(status) {
        switch (status) {
            case 'Completed': return 'success';
            case 'Cancelled': return 'danger';
            case 'Dispatched': return 'info';
            case 'In Progress': return 'warning';
            default: return 'secondary';
        }
    }

    /* ─── Confirm Actions ─── */
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });

    /* ─── Sort Indicators ─── */
    document.querySelectorAll('.trip-table th a').forEach(function (link) {
        var icon = link.querySelector('.fa-sort') || link.querySelector('.fa-sort-up, .fa-sort-down');
        if (icon) {
            var href = link.getAttribute('href');
            if (href) {
                if (href.indexOf('direction=ASC') > -1) {
                    icon.className = 'fa fa-sort-up';
                } else if (href.indexOf('direction=DESC') > -1) {
                    icon.className = 'fa fa-sort-down';
                }
            }
        }
    });

    /* ─── Helper: Escape HTML ─── */
    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(str));
        return d.innerHTML;
    }

    /* ─── Close suggestions on outside click ─── */
    document.addEventListener('click', function (e) {
        var container = document.getElementById('suggestions-container');
        if (container && !e.target.closest('[data-trip-suggest]') && !e.target.closest('#suggestions-container')) {
            container.style.display = 'none';
        }
    });
});
