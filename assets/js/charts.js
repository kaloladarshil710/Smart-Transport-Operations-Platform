document.addEventListener('DOMContentLoaded', function () {
  const canvas = document.getElementById('dashboard-chart');
  if (!canvas) {
    return;
  }

  const ctx = canvas.getContext('2d');
  new window.Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
      datasets: [{
        label: 'Trips',
        data: [12, 19, 14, 17, 22, 25],
        backgroundColor: '#2563EB'
      }]
    },
    options: { responsive: true }
  });
});
