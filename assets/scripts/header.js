(function () {
  const menuToggle = document.getElementById('menuToggle');
  const mobileNav = document.getElementById('mobileNav');

  if (!menuToggle || !mobileNav) {
    return;
  }

  menuToggle.addEventListener('click', function (event) {
    event.preventDefault();
    menuToggle.classList.toggle('active');
    mobileNav.classList.toggle('active');
    document.body.classList.toggle('active');
  });
})();
