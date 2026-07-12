document.addEventListener('DOMContentLoaded', function () {
  const saved = localStorage.getItem('transitops-theme');
  if (saved === 'dark') {
    document.body.classList.add('dark');
  }
  const toggle = document.getElementById('theme-toggle');
  if (toggle) {
    const sync = () => { const dark = document.body.classList.contains('dark'); toggle.setAttribute('aria-label', dark ? 'Enable light mode' : 'Enable dark mode'); toggle.querySelector('i').className = dark ? 'fa fa-sun-o' : 'fa fa-moon-o'; };
    sync(); toggle.addEventListener('click', () => { document.body.classList.toggle('dark'); localStorage.setItem('transitops-theme', document.body.classList.contains('dark') ? 'dark' : 'light'); sync(); });
  }
});
