// hero image fallback for environments with root-path issues
const heroImages = document.querySelectorAll('section.hero img');
heroImages.forEach((img) => {
  img.addEventListener('error', () => {
    const src = img.getAttribute('src') || '';
    if (src.startsWith('/assets/')) {
      img.setAttribute('src', src.replace('/assets/', '../assets/'));
    }
  });
});

// Setting Hero Height and Mobile Nav height
const heroSection = document.querySelector('.hero');
const header = document.querySelector('header');
const mobileNav = document.querySelector('.mobile-nav');
const mobileNavList = mobileNav ? mobileNav.querySelector('ul') : null;
document.addEventListener('DOMContentLoaded', function() {
    function adjustHeroHeight() {
      if (header && heroSection) {
          const headerHeight = header.offsetHeight;
          const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
          const computedHeight = Math.max(560, viewportHeight - headerHeight);
          heroSection.style.height = `${computedHeight}px`;
      }

      if (header && mobileNavList) {
          const headerHeight = header.offsetHeight;
          mobileNavList.style.paddingTop = `calc(64px + ${headerHeight}px)`;
      }
  }

    adjustHeroHeight();
    window.addEventListener('resize', adjustHeroHeight);
});




const heroSwiperEl = document.querySelector('.swiper-1');
if (heroSwiperEl && typeof Swiper !== 'undefined') {
  const swiper1 = new Swiper('.swiper-1', {
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

const swiper2 = new Swiper('.swiper-2', {
  direction: 'horizontal',
  loop: true,
  speed: 1000,
  slidesPerView: 1,
  spaceBetween: 36,
  autoplay: {
      delay: 5000
  },
  breakpoints: {
    // when window width is <= 750px
    // when window width is <= 1000px
    750: {
        slidesPerView: 1,
    },
    1000: {
      slidesPerView: 2,
    },
    // when window width is <= 1200px
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


  function fetchFinancialNews(apiKey) {
    const url = 'https://finnhub.io/api/v1/news';
    const params = {
      token: apiKey,
      category: 'general' // Adjust category as needed
    };
    const queryString = new URLSearchParams(params).toString();
  
    return fetch(`${url}?${queryString}`)
      .then(response => response.json())
      .then(data => {
        // Extract the first 10 news articles
        return data.slice(0, 10);
      })
      .catch(error => {
        console.error('Error fetching news:', error);
        return [];
      });
  }
  
  // Example usage
  const apiKey = 'cqta661r01qvdch30k6gcqta661r01qvdch30k70';
  fetchFinancialNews(apiKey)
    .then(newsData => {
        console.log(newsData);
        const tickerWrapper = document.querySelector('.latest-news .ticker-wrapper');
        newsData.forEach(article => {
            const span = document.createElement('span');
            span.textContent = article.headline;
            tickerWrapper.appendChild(span);
        });
        const clone = tickerWrapper.cloneNode(true);
        tickerWrapper.appendChild(clone);
        
        tickerWrapper.style.whiteSpace = 'nowrap';
        tickerWrapper.style.overflow = 'hidden';
        
        let tickerWidth = tickerWrapper.scrollWidth;
        tickerWrapper.style.width = `${tickerWidth * 2}px`; // Double the width for continuous loop
        
        let scrollPosition = 0;
        const scrollSpeed = 1; // Adjust scroll speed as needed
        
        function scrollTicker() {
            scrollPosition -= scrollSpeed;
            tickerWrapper.style.transform = `translateX(${scrollPosition}px)`;
        
            if (Math.abs(scrollPosition) >= tickerWidth) {
            scrollPosition = 0;
            tickerWrapper.style.transform = `translateX(${scrollPosition}px)`;
            }
        
            requestAnimationFrame(scrollTicker);
        }
        
        scrollTicker();
    });

const toggleBox = document.querySelector('.toggle-box');
const toggle = toggleBox ? toggleBox.querySelector('.toggle') : null;
const salaryInput = document.getElementById('salaryInput');
const salaryLiveInput = document.getElementById('salaryLiveInput');
const loanAmount = document.getElementById('loanAmount');
const loanFee = document.getElementById('loanFee');
const loanTotalRepayment = document.getElementById('loanTotalRepayment');
const loanInstallment = document.getElementById('loanInstallment');
const loanTenorDays = document.getElementById('loanTenorDays');
const loanTenorDisplay = document.getElementById('loanTenorDisplay');
const applyLoanLink = document.getElementById('applyLoanLink');
const allowedTenors = [14, 30, 60];

if (toggleBox && toggle && salaryInput && loanAmount) {
    function getCenter() {
        return {
            x: toggleBox.offsetWidth / 2,
            y: toggleBox.offsetHeight / 2,
        };
    }

    function getAngleBetweenPoints(cx, cy, ex, ey) {
        const radians = Math.atan2(ey - cy, ex - cx);
        return radians * (180 / Math.PI);
    }

    function angleToSalary(angle) {
        const normalizedAngle = (angle % 360 + 360) % 360;
        return Math.round((normalizedAngle / 360) * 50000);
    }

    function salaryToAngle(salary) {
        const safeSalary = Math.max(0, Math.min(50000, salary));
        return (safeSalary / 50000) * 360;
    }

    function formatCurrency(value) {
        return Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function getFeeRateByTenor(tenorDays) {
        if (tenorDays <= 14) return 0.08;
        if (tenorDays <= 30) return 0.12;
        return 0.18;
    }

    function updateCalculator(salary) {
        const safeSalary = Math.max(0, Math.min(50000, Math.round(salary)));
        const selectedTenor = Number(loanTenorDays?.value || 30);
        const tenor = allowedTenors.includes(selectedTenor) ? selectedTenor : 30;

        salaryInput.textContent = safeSalary.toLocaleString('en-US');
        if (salaryLiveInput && document.activeElement !== salaryLiveInput) {
            salaryLiveInput.value = safeSalary;
        }

        const principal = Math.max(0, Math.min(safeSalary * 0.4, 12000));
        const feeRate = getFeeRateByTenor(tenor);
        const fee = principal * feeRate;
        const totalRepayment = principal + fee;
        const monthlyEquivalent = tenor > 0 ? (totalRepayment / tenor) * 30 : totalRepayment;

        loanAmount.textContent = formatCurrency(principal);
        if (loanFee) loanFee.textContent = formatCurrency(fee);
        if (loanTotalRepayment) loanTotalRepayment.textContent = formatCurrency(totalRepayment);
        if (loanInstallment) loanInstallment.textContent = formatCurrency(monthlyEquivalent);
        if (loanTenorDisplay) loanTenorDisplay.textContent = tenor;

        if (applyLoanLink) {
            const query = new URLSearchParams({
                salary: String(safeSalary),
                amount: principal.toFixed(2),
                fee: fee.toFixed(2),
                tenor: String(tenor),
                repayment: totalRepayment.toFixed(2),
                installment: monthlyEquivalent.toFixed(2),
            });
            applyLoanLink.href = `/loan/?${query.toString()}`;
            const isValidEstimate = safeSalary >= 500 && principal > 0;
            applyLoanLink.classList.toggle('is-disabled', !isValidEstimate);
            applyLoanLink.setAttribute('aria-disabled', isValidEstimate ? 'false' : 'true');
        }

        const angle = salaryToAngle(Math.min(safeSalary, 15000));
        toggle.style.transform = `rotate(${angle}deg)`;
    }

    function onPointerMove(event) {
        let x;
        let y;

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

        const center = getCenter();
        const angle = getAngleBetweenPoints(center.x, center.y, pointerX, pointerY);
        const calcAngle = angle + 90;
        const salary = angleToSalary(calcAngle);

        updateCalculator(salary);
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

    if (salaryLiveInput) {
        salaryLiveInput.addEventListener('input', () => {
            updateCalculator(Number(salaryLiveInput.value) || 0);
        });
    }

    if (loanTenorDays) {
        loanTenorDays.addEventListener('change', () => {
            updateCalculator(Number(salaryLiveInput?.value || salaryInput.textContent.replace(/,/g, '')) || 0);
        });
    }

    updateCalculator(Number(salaryLiveInput?.value) || 0);
}


// Benefits Section JS Functions
const benefitsBlock = document.querySelector('section.benefits');
const benefitsImages = benefitsBlock ? benefitsBlock.querySelectorAll('.left img') : [];
const benefitsTextBlocks = benefitsBlock ? benefitsBlock.querySelectorAll('.right .benefit') : [];
const benefitsLeftToggle = benefitsBlock ? benefitsBlock.querySelector('.center .left') : null;
const benefitsRightToggle = benefitsBlock ? benefitsBlock.querySelector('.center .right') : null;
const benefitFallbackImage = '/assets/images/placeholder-image.png';
benefitsImages.forEach((img) => {
  img.addEventListener('error', () => {
    img.onerror = null;
    img.src = benefitFallbackImage;
    if (benefitsBlock) {
      const leftPanel = benefitsBlock.querySelector('.benefits .left');
      if (leftPanel) {
        leftPanel.style.background = '#e9eff6';
      }
    }
  });
});


const benefitsImageProperties = [
  {
    number: 0,
    left: '0',
    zIndex: '2',
  },
  {
    number: 1,
    left: '100%',
    zIndex: '1',
  },
  {
    number: 2,
    left: '200%',
    zIndex: '0',
  }
];

let benefitsState = 0;
let autoSlideInterval;

// Function to set the state of the benefits section
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

// Function to move to the next slide (simulates right toggle click)
function nextSlide() {
  benefitsState = (benefitsState + 1) % 3;
  setBenefitsState();
}

// Function to reset the auto-slide interval
function resetInterval() {
  clearInterval(autoSlideInterval);
  autoSlideInterval = setInterval(nextSlide, 5000);
}

if (benefitsImages.length && benefitsTextBlocks.length && benefitsLeftToggle && benefitsRightToggle) {
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

// FAQ accordion
const faqItems = document.querySelectorAll('#faqAccordion .faq-item');
if (faqItems.length) {
  faqItems.forEach((item, idx) => {
    const question = item.querySelector('.faq-question');
    const answer = item.querySelector('.faq-answer');
    if (!question || !answer) return;

    const answerId = `faqAnswer${idx + 1}`;
    question.setAttribute('aria-controls', answerId);
    answer.setAttribute('id', answerId);
    answer.setAttribute('role', 'region');
    answer.setAttribute('aria-hidden', 'true');

    if (idx === 0) {
      item.classList.add('active');
      question.setAttribute('aria-expanded', 'true');
      answer.setAttribute('aria-hidden', 'false');
      answer.style.maxHeight = `${answer.scrollHeight}px`;
    }

    question.addEventListener('click', () => {
      const isOpen = item.classList.contains('active');

      faqItems.forEach((other) => {
        other.classList.remove('active');
        const otherQ = other.querySelector('.faq-question');
        const otherA = other.querySelector('.faq-answer');
        if (otherQ) otherQ.setAttribute('aria-expanded', 'false');
        if (otherA) {
          otherA.style.maxHeight = '0px';
          otherA.setAttribute('aria-hidden', 'true');
        }
      });

      if (!isOpen) {
        item.classList.add('active');
        question.setAttribute('aria-expanded', 'true');
        answer.style.maxHeight = `${answer.scrollHeight}px`;
        answer.setAttribute('aria-hidden', 'false');
      }
    });
  });
}
