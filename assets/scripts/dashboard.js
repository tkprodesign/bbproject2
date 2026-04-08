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
document.querySelectorAll('.dashboard-header .menu-item, .dashboard-header .submenu-link').forEach((link) => {
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
const nav = document.querySelector('.dashboard-header .mobile-drawer');
const menuToggle = document.getElementById('menuToggle');

if (nav && menuToggle) {
  const header = document.querySelector('.dashboard-header');

  const setMenuState = (isActive) => {
    nav.classList.toggle('active', isActive);
    menuToggle.classList.toggle('active', isActive);
    menuToggle.setAttribute('aria-expanded', isActive ? 'true' : 'false');
    if (header) {
      header.classList.toggle('nav-open', isActive);
    }
    document.documentElement.classList.toggle('menu-open', isActive);
    document.body.classList.toggle('menu-open', isActive);
  };

  menuToggle.addEventListener('click', (event) => {
    event.preventDefault();
    setMenuState(!nav.classList.contains('active'));
  });

  nav.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      setMenuState(false);
    });
  });

  document.addEventListener('click', (event) => {
    const clickedInsideMenu = nav.contains(event.target) || menuToggle.contains(event.target);
    if (!clickedInsideMenu) {
      setMenuState(false);
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      setMenuState(false);
    }
  });
}

// Submenu behavior (desktop + mobile)
document.querySelectorAll('.dashboard-header [data-submenu]').forEach((group) => {
  const trigger = group.querySelector('.menu-trigger');
  if (!trigger) return;

  trigger.addEventListener('click', (event) => {
    event.preventDefault();
    const isMobileGroup = group.classList.contains('is-mobile');
    if (!isMobileGroup && window.matchMedia('(min-width: 1001px)').matches) {
      return;
    }

    const isActive = group.classList.toggle('active');
    trigger.setAttribute('aria-expanded', isActive ? 'true' : 'false');

    if (isMobileGroup && isActive) {
      nav?.querySelectorAll('[data-submenu].is-mobile').forEach((otherGroup) => {
        if (otherGroup !== group) {
          otherGroup.classList.remove('active');
          otherGroup.querySelector('.menu-trigger')?.setAttribute('aria-expanded', 'false');
        }
      });
    }
  });

  if (!group.classList.contains('is-mobile')) {
    group.addEventListener('mouseleave', () => {
      group.classList.remove('active');
      trigger.setAttribute('aria-expanded', 'false');
    });
  }
});

// Transactions search/filter/export UX
const txTable = document.getElementById('transactionsTable');
if (txTable) {
  const searchInput = document.getElementById('txSearchInput');
  const filterButtons = document.querySelectorAll('.tx-filter');
  const emptyState = document.getElementById('txEmptyState');
  const exportButton = document.getElementById('txExportBtn');
  let activeFilter = 'all';

  const applyTxFilters = () => {
    const query = (searchInput?.value || '').trim().toLowerCase();
    let visibleCount = 0;

    txTable.querySelectorAll('tbody tr').forEach((row) => {
      const rowText = row.innerText.toLowerCase();
      const txType = row.getAttribute('data-tx-type') || 'all';
      const typeMatch = activeFilter === 'all' || activeFilter === txType;
      const searchMatch = !query || rowText.includes(query);
      const isVisible = typeMatch && searchMatch;
      row.style.display = isVisible ? '' : 'none';
      if (isVisible) visibleCount++;
    });

    if (emptyState) {
      emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  };

  if (searchInput) {
    searchInput.addEventListener('input', applyTxFilters);
  }

  filterButtons.forEach((button) => {
    button.addEventListener('click', () => {
      activeFilter = button.getAttribute('data-filter') || 'all';
      filterButtons.forEach((btn) => btn.classList.remove('active'));
      button.classList.add('active');
      applyTxFilters();
    });
  });

  if (exportButton) {
    exportButton.addEventListener('click', () => {
      const rows = [...txTable.querySelectorAll('tr')].filter((row) => row.style.display !== 'none');
      const csv = rows
        .map((row) => [...row.querySelectorAll('th, td')]
          .map((cell) => `"${cell.innerText.replace(/"/g, '""')}"`)
          .join(','))
        .join('\n');

      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.setAttribute('href', url);
      link.setAttribute('download', 'transactions.csv');
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);
    });
  }
}
