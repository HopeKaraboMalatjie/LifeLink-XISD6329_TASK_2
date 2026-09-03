// LifeLink — shared front-end behaviour (Task 2 will wire this up to api/*.php)

document.addEventListener("DOMContentLoaded", () => {
  console.log("LifeLink front-end loaded — API integration comes in Task 2.");

  // Example: highlight the current page in the navbar
  const links = document.querySelectorAll(".navbar nav a");
  const current = window.location.pathname.split("/").pop();
  links.forEach((link) => {
    if (link.getAttribute("href") === current) {
      link.style.textDecoration = "underline";
    }
  });
});
