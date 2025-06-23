document.addEventListener("DOMContentLoaded", () => {
  const canvas = document.getElementById("overlay");
  const ctx = canvas.getContext("2d");

  const colors = {
    child: 'rgb(3, 206, 206)',
    accident: 'rgb(220, 53, 69)',
    user: 'rgb(255, 2, 141)'
  };
  const INTERVAL = 3000;
  
  // original dimension
  const ORIGINAL_WIDTH = 1080;
  const ORIGINAL_HEIGHT = 1080;
  
  function scaleCoordinates(x, y) {
    const scaleX = canvas.width / ORIGINAL_WIDTH;
    const scaleY = canvas.height / ORIGINAL_HEIGHT;
    return {
      x: x * scaleX,
      y: y * scaleY
    };
  }
  
  function scaleSize(size) {
    const scale = Math.min(canvas.width / ORIGINAL_WIDTH, canvas.height / ORIGINAL_HEIGHT);
    return Math.max(size * scale, 3); 
  }
  
  function resizeCanvas() {
    const container = canvas.parentElement;
    const containerRect = container.getBoundingClientRect();
    
    const aspectRatio = ORIGINAL_WIDTH / ORIGINAL_HEIGHT;
    
    let newWidth = containerRect.width;
    let newHeight = newWidth / aspectRatio;
    
    if (newHeight > containerRect.height) {
      newHeight = containerRect.height;
      newWidth = newHeight * aspectRatio;
    }
    
    canvas.width = newWidth;
    canvas.height = newHeight;
    
    canvas.style.width = newWidth + 'px';
    canvas.style.height = newHeight + 'px';
  }

  function drawDot(x, y, color, radius = 6) {
    const scaled = scaleCoordinates(x, y);
    const scaledRadius = scaleSize(radius);
    
    ctx.fillStyle = color;//set fill color
    ctx.beginPath();//start a new shape
    ctx.arc(scaled.x, scaled.y, scaledRadius, 0, 2 * Math.PI);//draw a circle centered at (x,y)
    ctx.fill();//actually fill it in
  }

  //used for drawing accidents on the fortnut map
  function drawX(x, y, color, size = 6, thickness = 4) {
    const scaled = scaleCoordinates(x, y);
    const scaledSize = scaleSize(size);
    const scaledThickness = scaleSize(thickness);
    
    ctx.strokeStyle = color;//set line color
    ctx.lineWidth = scaledThickness;
    ctx.beginPath();//start a new shape
    ctx.moveTo(scaled.x - scaledSize, scaled.y - scaledSize);
    ctx.lineTo(scaled.x + scaledSize, scaled.y + scaledSize);
    ctx.moveTo(scaled.x + scaledSize, scaled.y - scaledSize);
    ctx.lineTo(scaled.x - scaledSize, scaled.y + scaledSize);
    ctx.stroke();//draw the two diagonal lines
  }

  function drawLabel(x, y, text, color = 'white') {
    const scaled = scaleCoordinates(x, y);
    const fontSize = Math.max(scaleSize(14), 10); // Minimum 10px pentru lizibilitate
    
    ctx.font = `${fontSize}px "Helvetica Neue"`;
    const padding = scaleSize(4);
    const lineH = fontSize * 0.8;//approx text height
    const textW = ctx.measureText(text).width;
  
    //box dimensions
    const boxW = textW + padding * 2;
    const boxH = lineH + padding * 2;
    const boxX = scaled.x - boxW / 2;
    const boxY = scaled.y - scaleSize(10) - boxH;//10px above the dot
  
    //draw semi-transparent box
    ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
    ctx.fillRect(boxX, boxY, boxW, boxH);
  
    //draw centered text
    ctx.fillStyle = color;
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText(text, scaled.x, boxY + boxH / 2);
  }

  //lagged implementation
  /*function updateOverlay() {
    fetch("images/overlaymap.php")//getting the info from results in overlaymap.php
      .then(res => res.json())
      .then(data => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        data.children.forEach(child => {
          drawDot(child.x, child.y, colors.child, 6);
          if (child.name) {
            const [firstName, lastName] = child.name.split(' ');//remove if label not working
            drawLabel(child.x, child.y, firstName, 'white');//change firstName to child.name if not working
          }
        });

        data.accidents.forEach(acc => {
          drawX(acc.x, acc.y, colors.accident);
        });

        if (data.user) {
          drawDot(data.user.x, data.user.y, colors.user, 8);
        }
      })
      .catch(err => console.error("Eroare overlay fetch:", err));
  }

  updateOverlay();
  setInterval(updateOverlay, 2000);*/


  //smooth implementation
  let prevKids = [], nextKids = [], lastFetch = performance.now();
  let accidents = [], userPos = null;

  //fetch new data
  function updateData() {
    fetch("images/overlaymap.php")
      .then(r => r.json())
      .then(data => {
        //when we fetch new values, we put in next the new info about kids and in prev what was before in next
        prevKids = nextKids;
        nextKids = data.children;
        accidents = data.accidents;
        userPos = data.user;
        lastFetch = performance.now();
      })
      .catch(e => console.error(e));
  }

  //smooth transition
  function animate() {
    const now = performance.now();
    const t = Math.min((now - lastFetch)/INTERVAL, 1);

    ctx.clearRect(0, 0, canvas.width, canvas.height);

    nextKids.forEach(k => {
      const prev = prevKids.find(p => p.name === k.name) || k; //if k in next exists in prev as well, we put in prev its old info
      const x = prev.x + (k.x - prev.x) * t;
      const y = prev.y + (k.y - prev.y) * t;
      drawDot(x, y, colors.child);
      const [firstName] = k.name.split(' ');
      drawLabel(x, y, firstName);
    });

    accidents.forEach(a => drawX(a.x, a.y, colors.accident));

    if (userPos) 
      drawDot(userPos.x, userPos.y, colors.user, 8);

    requestAnimationFrame(animate);
  }

  window.addEventListener('resize', () => {
    resizeCanvas();
  });
  
  window.addEventListener('orientationchange', () => {
    setTimeout(resizeCanvas, 100); 
  });

  resizeCanvas();
  setInterval(updateData, INTERVAL);//repeat after specific interval
  updateData();//initial fetch
  requestAnimationFrame(animate);
});