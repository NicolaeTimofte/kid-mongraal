document.addEventListener('DOMContentLoaded', () => {
  const items = document.querySelectorAll('#features-list3 li');
  const colors = ['#b14fd3', '#5c8f9e'];  // purple, blue
  let idx = 0;

  function highlightNext() {
    items.forEach(li => {
      li.classList.remove('active-purple', 'active-blue');
    });

    const active = items[idx];
    // pick color name based on even/odd index
    const colorClass = (idx % 2 === 0) ? 'active-purple' : 'active-blue';
    active.classList.add(colorClass);

    idx = (idx + 1) % items.length;
  }

  highlightNext();
  setInterval(highlightNext, 4000);
});