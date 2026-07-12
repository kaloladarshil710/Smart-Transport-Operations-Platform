document.addEventListener('DOMContentLoaded', function () {
  const forms = document.querySelectorAll('form[data-validate="true"]');
  forms.forEach(function (form) {
    form.addEventListener('submit', function (event) {
      const required = form.querySelectorAll('[required]');
      let valid = true;
      required.forEach(function (field) {
        if (!field.value.trim()) {
          valid = false;
          field.style.borderColor = 'var(--danger)';
        }
      });
      if (!valid) {
        event.preventDefault();
      }
    });
  });
  document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
      const input = document.getElementById(button.dataset.passwordToggle);
      if (!input) return;
      const reveal = input.type === 'password'; input.type = reveal ? 'text' : 'password';
      button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
      button.querySelector('i').className = reveal ? 'fa fa-eye-slash' : 'fa fa-eye';
    });
  });
});
