document.addEventListener('DOMContentLoaded', function () {
  const flash = document.querySelector('.flash-message');
  if (flash) {
    setTimeout(function () { flash.remove(); }, 4000);
  }
  document.querySelectorAll('form[data-validate="true"]').forEach(function (form) {
    form.addEventListener('submit', function () {
      if (form.checkValidity()) {
        const submit = form.querySelector('[type="submit"]');
        if (submit) submit.setAttribute('aria-busy', 'true');
      }
    });
  });
});
