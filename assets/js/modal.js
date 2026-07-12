document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-modal-open]').forEach(function (button) {
    button.addEventListener('click', function () {
      const modalId = button.getAttribute('data-modal-open');
      const modal = document.getElementById(modalId);
      if (modal) {
        modal.style.display = 'block';
      }
    });
  });
});
