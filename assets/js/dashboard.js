document.addEventListener('DOMContentLoaded', function () {
  const cards = document.querySelectorAll('.stat-card');
  cards.forEach(function (card, index) {
    card.style.animationDelay = (index * 80) + 'ms';
    card.classList.add('fade-in');
  });
});
