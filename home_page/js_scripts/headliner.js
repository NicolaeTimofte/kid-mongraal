/*document.addEventListener("DOMContentLoaded", () => {
  const msg = document.getElementById("hero-message");

  const obs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        msg.classList.add("visible");
        obs.disconnect(); //only once
      }
    });
  },{
    rootMargin: "0px 0px -20% 0px" //trigger just before it's fully in view
  });

  obs.observe(msg);
});*/
document.addEventListener("DOMContentLoaded", () => {
  const sections = document.querySelectorAll(".hero-message");

  const obs = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;

      entry.target.classList.add("visible");
      observer.unobserve(entry.target);
    });
  }, {
    rootMargin: "0px 0px -20% 0px"
  });

  sections.forEach(sec => obs.observe(sec));
});