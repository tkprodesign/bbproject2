// Setting Hero Height and Mobile Nav height
const heroSection = document.querySelector('.hero');
const header = document.querySelector('header');
const mobileNav = document.querySelector('.mobile-nav');
const mobileNavList = mobileNav ? mobileNav.querySelector('ul') : null;
const documentBody = document.querySelector('body');

function adjustHeroHeight() {
  if (!header || !heroSection) return;

  const headerHeight = header.offsetHeight;

  if (window.innerWidth <= 1000) {
    heroSection.style.height = '600px';
  } else {
    heroSection.style.height = `calc(100vh - ${headerHeight}px)`;
  }

  if (mobileNavList) {
    mobileNavList.style.paddingTop = `calc(64px + ${headerHeight}px)`;
  }
}

document.addEventListener('DOMContentLoaded', function() {
  adjustHeroHeight();
  window.addEventListener('resize', adjustHeroHeight);
});

// Menu toggle functions
const menuToggle = document.querySelector('#menuToggle');
if (menuToggle && mobileNav && documentBody) {
  menuToggle.addEventListener('click', (event) => {
    event.preventDefault();
    menuToggle.classList.toggle('active');
    mobileNav.classList.toggle('active');
    documentBody.classList.toggle('active');
  });
}

if (document.querySelector('.swiper-1')) {
  new Swiper('.swiper-1', {
    direction: 'horizontal',
    loop: true,
    speed: 1000,
    autoplay: {
      delay: 5000
    },
    pagination: {
      el: '.swiper-pagination-1',
    },
    navigation: {
      nextEl: '.swiper-button-next-1',
      prevEl: '.swiper-button-prev-1',
    },
    scrollbar: {
      el: '.swiper-scrollbar-1',
    },
  });
}

if (document.querySelector('.swiper-2')) {
  new Swiper('.swiper-2', {
    direction: 'horizontal',
    loop: true,
    speed: 1000,
    slidesPerView: 1,
    spaceBetween: 36,
    autoplay: {
      delay: 5000
    },
    breakpoints: {
      750: {
        slidesPerView: 1,
      },
      1000: {
        slidesPerView: 2,
      },
      1200: {
        slidesPerView: 3,
      },
    },
    pagination: {
      el: '.swiper-pagination-2',
    },
    navigation: {
      nextEl: '.swiper-button-next-2',
      prevEl: '.swiper-button-prev-2',
    },
    scrollbar: {
      el: '.swiper-scrollbar-2',
    },
  });
}

function fetchFinancialNews(apiKey) {
  const url = 'https://finnhub.io/api/v1/news';
  const params = {
    token: apiKey,
    category: 'general'
  };
  const queryString = new URLSearchParams(params).toString();

  return fetch(`${url}?${queryString}`)
    .then(response => response.json())
    .then(data => data.slice(0, 10))
    .catch(() => []);
}

const tickerWrapper = document.querySelector('.latest-news .ticker-wrapper');
if (tickerWrapper) {
  const apiKey = 'cqta661r01qvdch30k6gcqta661r01qvdch30k70';
  fetchFinancialNews(apiKey).then(newsData => {
    newsData.forEach(article => {
      const span = document.createElement('span');
      span.textContent = article.headline;
      tickerWrapper.appendChild(span);
    });

    const clone = tickerWrapper.cloneNode(true);
    tickerWrapper.appendChild(clone);

    tickerWrapper.style.whiteSpace = 'nowrap';
    tickerWrapper.style.overflow = 'hidden';

    const tickerWidth = tickerWrapper.scrollWidth;
    tickerWrapper.style.width = `${tickerWidth * 2}px`;

    let scrollPosition = 0;
    const scrollSpeed = 1;

    function scrollTicker() {
      scrollPosition -= scrollSpeed;
      tickerWrapper.style.transform = `translateX(${scrollPosition}px)`;

      if (Math.abs(scrollPosition) >= tickerWidth) {
        scrollPosition = 0;
        tickerWrapper.style.transform = 'translateX(0)';
      }

      requestAnimationFrame(scrollTicker);
    }

    scrollTicker();
  });
}

const toggleBox = document.querySelector('.toggle-box');
const toggle = toggleBox ? toggleBox.querySelector('.toggle') : null;
const salaryInput = document.getElementById('salaryInput');
const loanAmount = document.getElementById('loanAmount');

if (toggleBox && toggle && salaryInput && loanAmount) {
  const center = {
    x: toggleBox.offsetWidth / 2,
    y: toggleBox.offsetHeight / 2
  };

  function getAngleBetweenPoints(cx, cy, ex, ey) {
    const radians = Math.atan2(ey - cy, ex - cx);
    return radians * (180 / Math.PI);
  }

  function angleToSalary(angle) {
    const normalizedAngle = (angle % 360 + 360) % 360;
    return Math.round((normalizedAngle / 360) * 15000);
  }

  function onPointerMove(event) {
    let x, y;
    if (event.type === 'mousemove') {
      x = event.clientX;
      y = event.clientY;
    } else if (event.type === 'touchmove') {
      const touch = event.touches[0];
      x = touch.clientX;
      y = touch.clientY;
    }

    const boxRect = toggleBox.getBoundingClientRect();
    const pointerX = x - boxRect.left;
    const pointerY = y - boxRect.top;

    const angle = getAngleBetweenPoints(center.x, center.y, pointerX, pointerY);
    const calcAngle = angle + 90;

    toggle.style.transform = `rotate(${calcAngle}deg)`;

    const salary = angleToSalary(calcAngle);
    salaryInput.textContent = salary;

    const loan = (salary * 0.7).toFixed(2);
    loanAmount.textContent = `${loan}`;
  }

  function onPointerEnter(event) {
    if (event.type === 'touchstart') {
      event.preventDefault();
    }
    toggleBox.addEventListener('mousemove', onPointerMove);
    toggleBox.addEventListener('touchmove', onPointerMove);
  }

  function onPointerLeave() {
    toggleBox.removeEventListener('mousemove', onPointerMove);
    toggleBox.removeEventListener('touchmove', onPointerMove);
  }

  toggleBox.addEventListener('mouseenter', onPointerEnter);
  toggleBox.addEventListener('mouseleave', onPointerLeave);
  toggleBox.addEventListener('touchstart', onPointerEnter);
  toggleBox.addEventListener('touchend', onPointerLeave);
}

// Benefits Section JS Functions
const benefitsBlock = document.querySelector('section.benefits');
if (benefitsBlock) {
  const benefitsImages = benefitsBlock.querySelectorAll('.left img');
  const benefitsTextBlocks = benefitsBlock.querySelectorAll('.right .benefit');
  const benefitsLeftToggle = benefitsBlock.querySelector('.center .left');
  const benefitsRightToggle = benefitsBlock.querySelector('.center .right');

  if (benefitsImages.length && benefitsTextBlocks.length && benefitsLeftToggle && benefitsRightToggle) {
    const benefitsImageProperties = [
      { number: 0, left: '0', zIndex: '2' },
      { number: 1, left: '100%', zIndex: '1' },
      { number: 2, left: '200%', zIndex: '0' }
    ];

    let benefitsState = 0;
    let autoSlideInterval;

    function setBenefitsState() {
      benefitsImages.forEach((img, index) => {
        const stateIndex = (benefitsState + index) % 3;
        img.style.left = benefitsImageProperties[stateIndex].left;
        img.style.zIndex = benefitsImageProperties[stateIndex].zIndex;
        img.style.transition = 'all 0.5s ease';
      });

      benefitsTextBlocks.forEach((textBlock, index) => {
        textBlock.style.display = index === benefitsState ? 'block' : 'none';
      });
    }

    function nextSlide() {
      benefitsState = (benefitsState + 1) % 3;
      setBenefitsState();
    }

    function resetInterval() {
      clearInterval(autoSlideInterval);
      autoSlideInterval = setInterval(nextSlide, 5000);
    }

    benefitsLeftToggle.addEventListener('click', () => {
      benefitsState = (benefitsState - 1 + 3) % 3;
      setBenefitsState();
      resetInterval();
    });

    benefitsRightToggle.addEventListener('click', () => {
      benefitsState = (benefitsState + 1) % 3;
      setBenefitsState();
      resetInterval();
    });

    setBenefitsState();
    autoSlideInterval = setInterval(nextSlide, 5000);
  }
}
