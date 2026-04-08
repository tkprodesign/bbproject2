document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.getElementById('menuToggle');
  const mobileNav = document.getElementById('mobileNav');
  const mobileNavOverlay = document.getElementById('mobileNavOverlay');

  if (!menuToggle || !mobileNav) return;

  let lockedScrollY = 0;

  const lockPageScroll = () => {
    lockedScrollY = window.scrollY || window.pageYOffset || 0;
    document.documentElement.classList.add('menu-open');
    document.body.classList.add('menu-open');
    document.body.style.position = 'fixed';
    document.body.style.top = `-${lockedScrollY}px`;
    document.body.style.width = '100%';
  };

  const unlockPageScroll = () => {
    document.documentElement.classList.remove('menu-open');
    document.body.classList.remove('menu-open');
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    window.scrollTo(0, lockedScrollY);
  };

  const setMenuState = (isOpen) => {
    menuToggle.classList.toggle('active', isOpen);
    menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    mobileNav.classList.toggle('active', isOpen);

    if (mobileNavOverlay) {
      mobileNavOverlay.classList.toggle('active', isOpen);
    }

    if (isOpen) {
      lockPageScroll();
    } else {
      unlockPageScroll();
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
