document.addEventListener("DOMContentLoaded", () => {
  const canvas = document.getElementById("overlay");
  const ctx = canvas.getContext("2d");

  const colors = {
    child: 'rgb(3, 206, 206)',
    accident: 'rgb(220, 53, 69)',
    user: 'rgb(255, 2, 141)'
  };

  let lastState = {
    children: [],
    accidents: [],
    user: null
  };

  function samePoint(p1, p2) {
    return p1 && p2 && p1.x === p2.x && p1.y === p2.y;
  }

  function drawDot(x, y, color, radius = 6) {
    ctx.fillStyle = color;
    ctx.beginPath();
    ctx.arc(x, y, radius, 0, 2 * Math.PI);
    ctx.fill();
  }

  function drawX(x, y, color, size = 6, thickness = 3) {
    ctx.strokeStyle = color;
    ctx.lineWidth = thickness;
    ctx.beginPath();
    ctx.moveTo(x - size, y - size);
    ctx.lineTo(x + size, y + size);
    ctx.moveTo(x + size, y - size);
    ctx.lineTo(x - size, y + size);
    ctx.stroke();
  }

  function updateOverlay() {
    fetch("images/overlaymap.php")
      .then(res => res.json())
      .then(data => {
        data.children.forEach(child => {
          if (!lastState.children.some(c => samePoint(c, child))) {
            drawDot(child.x, child.y, colors.child, 6);
            lastState.children.push(child);
          }
        });

        data.accidents.forEach(acc => {
          if (!lastState.accidents.some(a => samePoint(a, acc))) {
            drawX(acc.x, acc.y, colors.accident);
            lastState.accidents.push(acc);
          }
        });

        if (!samePoint(lastState.user, data.user)) {
          drawDot(data.user.x, data.user.y, colors.user, 8);
          lastState.user = data.user;
        }
      })
      .catch(err => console.error("Eroare overlay fetch:", err));
  }

  updateOverlay();
  setInterval(updateOverlay, 2000);
});
