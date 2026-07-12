document.addEventListener('DOMContentLoaded', function () {
  const saved = localStorage.getItem('transitops-theme');
  if (saved === 'dark') {
    document.body.classList.add('dark');
  }
});
