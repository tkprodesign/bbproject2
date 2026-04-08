document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.getElementById('menuToggle');
  const mobileNav = document.getElementById('mobileNav');
  const mobileNavOverlay = document.getElementById('mobileNavOverlay');

  if (!menuToggle || !mobileNav) return;

  const setMenuState = (isOpen) => {
    menuToggle.classList.toggle('active', isOpen);
    menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    mobileNav.classList.toggle('active', isOpen);
    document.body.classList.toggle('active', isOpen);
    if (mobileNavOverlay) {
      mobileNavOverlay.classList.toggle('active', isOpen);
    }
  };

  const toggleMenu = (event) => {
    event.preventDefault();
    event.stopPropagation();
    setMenuState(!mobileNav.classList.contains('active'));
  };

  menuToggle.addEventListener('click', toggleMenu);
  menuToggle.addEventListener('touchstart', toggleMenu, { passive: false });

  if (mobileNavOverlay) {
    mobileNavOverlay.addEventListener('click', () => setMenuState(false));
  }

  mobileNav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => setMenuState(false));
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setMenuState(false);
    }
  });
});
