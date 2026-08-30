document.addEventListener("DOMContentLoaded", function () {
  const button = document.getElementById("mobileMenuButton");

  const menu = document.getElementById("publicNavLinks");

  if (!button || !menu) {
    return;
  }

  button.addEventListener("click", function () {
    menu.classList.toggle("open");
  });

  const links = menu.querySelectorAll("a");

  links.forEach(function (link) {
    link.addEventListener("click", function () {
      menu.classList.remove("open");
    });
  });
});
