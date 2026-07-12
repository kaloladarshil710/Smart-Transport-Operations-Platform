document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-ajax-form]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(form);
      fetch(form.getAttribute('action') || window.location.href, {
        method: form.getAttribute('method') || 'POST',
        body: formData
      }).then(function (response) {
        return response.text();
      }).then(function () {
        window.location.reload();
      });
    });
  });
});
