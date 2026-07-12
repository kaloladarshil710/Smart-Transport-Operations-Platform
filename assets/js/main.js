document.addEventListener('DOMContentLoaded', function () {
  const flash = document.querySelector('.flash-message');
  if (flash) {
    setTimeout(function () { flash.remove(); }, 4000);
  }
});
