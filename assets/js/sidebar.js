document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.querySelector('.sidebar-toggle');
  if (toggle) {
    toggle.addEventListener('click', function () {
      const sidebar = document.querySelector('.sidebar');
      const compact = window.matchMedia('(max-width: 992px)').matches;
      document.body.classList.toggle('sidebar-collapsed', !compact);
      sidebar.classList.toggle('is-open', compact);
      toggle.setAttribute('aria-expanded', String(compact ? sidebar.classList.contains('is-open') : !document.body.classList.contains('sidebar-collapsed')));
    });
  }
});
