document.addEventListener('DOMContentLoaded', function () {
  const bell = document.querySelector('.icon-button');
  if (bell) {
    bell.addEventListener('click', function () {
      alert('Notification center is ready.');
    });
  }
});
