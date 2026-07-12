document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.querySelector('.sidebar-toggle');
  if (toggle) {
    toggle.addEventListener('click', function () {
      document.querySelector('.sidebar').style.display = document.querySelector('.sidebar').style.display === 'block' ? 'none' : 'block';
    });
  }
});
