/*document.addEventListener('DOMContentLoaded', () => {
  const items = document.querySelectorAll('#features-list li');
  let idx = 0;
  function highlightNext() {
    items.forEach((li,i) => li.classList.toggle('active', i === idx));
    idx = (idx + 1) % items.length;
  }
  //start immediately, then every 4 seconds
  highlightNext();
  setInterval(highlightNext, 4000);
});*/

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.highlight-list').forEach(ul => {
    const items = ul.querySelectorAll('li');
    let idx = 0;
    function highlightNext() {
      items.forEach((li,i) => li.classList.toggle('active', i === idx));
      idx = (idx + 1) % items.length;
    }
    highlightNext();
    setInterval(highlightNext, 4000);
  });
});