// Close pre-header alert
const preHeader = document.querySelector('.pre-header');
if (preHeader) {
  const preHeaderCTA = preHeader.querySelector('.close');
  if (preHeaderCTA) {
    preHeaderCTA.addEventListener('click', (event) => {
      event.preventDefault();
      preHeader.style.display = 'none';
    });
  }
}

// Set active CTA based on current path
const currentPath = window.location.pathname;
document.querySelectorAll('header .container > a, header nav > a').forEach((link) => {
  const href = link.getAttribute('href');
  if (!href || href === '#') return;

  if (currentPath === href || (href !== '/dashboard' && currentPath.startsWith(href))) {
    link.classList.add('active');
  }
});

// Sticky header
const header = document.querySelector('header');
if (header) {
  const sticky = header.offsetTop;
  window.addEventListener('scroll', () => {
    if (window.pageYOffset > sticky) {
      header.classList.add('sticky');
    } else {
      header.classList.remove('sticky');
    }
  });
}

// Menu toggle (dashboard)
const nav = document.querySelector('header nav');
const menuToggle = document.getElementById('menuToggle');

if (nav && menuToggle) {
  menuToggle.addEventListener('click', (event) => {
    event.preventDefault();
    nav.classList.toggle('active');
    menuToggle.classList.toggle('active');
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      nav.classList.remove('active');
      menuToggle.classList.remove('active');
    });
  });
}
