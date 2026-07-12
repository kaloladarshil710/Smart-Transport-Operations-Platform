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
});
