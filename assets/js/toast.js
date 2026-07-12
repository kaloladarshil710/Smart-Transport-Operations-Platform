/** Accessible, dependency-free toast notifications. */
window.TransitOps = window.TransitOps || {};
window.TransitOps.toast = function (message, duration) {
  const region = document.getElementById('toast-region');
  if (!region) return;
  const toast = document.createElement('div'); toast.className = 'toast'; toast.textContent = message;
  region.appendChild(toast); window.setTimeout(() => toast.remove(), duration || 4000);
};
