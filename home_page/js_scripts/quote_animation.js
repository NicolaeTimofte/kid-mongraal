(function(){
  const quotes = [
    "Because life pulls you away",
    "Real-time kid tracking",
    "Your child’s safety, simplified",
    "Be there-even when you’re not",
    "Hala Madrid!"
  ];
  const el = document.getElementById("quote");
  const scrambleChars = "@$#ABCDEFKLMNOPQRSTabcdefghijk@@lmnopqrstxyz0279()";
  let idx = 0;

  //produce a scrambled version of a string
  function scrambleText(str) {
    return str.split("")
              .map(c => c === " " ? " " : scrambleChars[Math.random()*scrambleChars.length|0])
              .join("");
  }

  //animate scramble reveal
  function animate(toText, onComplete) {
    el.className = "quote encrypted";
    const duration = 800;//scramble duration
    const frameRate = 60;//ms per frame
    const frames = duration / frameRate;
    let currentFrame = 0;

    const iv = setInterval(() => {
      if (++currentFrame < frames) {
        el.textContent = scrambleText(toText);
      } else {
        clearInterval(iv);
        el.className = "quote decrypted";
        el.textContent = toText;
        onComplete && onComplete();
      }
    }, frameRate);
  }

  function cycle() {
    animate(quotes[idx], () => {
      setTimeout(() => {
        idx = (idx + 1) % quotes.length;
        cycle();
      }, 5000);
    });
  }

  window.addEventListener("DOMContentLoaded", cycle);
})();