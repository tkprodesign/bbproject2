// Setting Hero Height and Mobile Nav height
const heroSection = document.querySelector('.hero');
const header = document.querySelector('header');
const mobileNav = document.querySelector('.mobile-nav')
const mobileNavList = mobileNav.querySelector('ul')
document.addEventListener('DOMContentLoaded', function() {
    // Select the section.hero and header elements
    // Function to set the height of .hero section
    function adjustHeroHeight() {
      if (header && heroSection) {
          // Get the height of the header
          const headerHeight = header.offsetHeight;
  
          // Check if the screen width is 100px or below
          if (window.innerWidth <= 1000) {
              // Set the height of the .hero section to 500px
                heroSection.style.height = '600px';
          } else {
              // Set the height of the .hero section as originally intended
              heroSection.style.height = `calc(100vh - ${headerHeight}px)`;
          }
  
          // Set padding-top of the mobile navigation list
          mobileNavList.style.paddingTop = `calc(64px + ${headerHeight}px)`;
      }
  }
  

    // Adjust the hero section height on page load
    adjustHeroHeight();

    // Optional: Adjust height on window resize
    window.addEventListener('resize', adjustHeroHeight);
});




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
    const toggle = toggleBox.querySelector('.toggle');
    const salaryInput = document.getElementById('salaryInput');
    const loanAmount = document.getElementById('loanAmount');
    
    const center = {
        x: toggleBox.offsetWidth / 2,
        y: toggleBox.offsetHeight / 2
    };
    
    function getAngleBetweenPoints(cx, cy, ex, ey) {
        const radians = Math.atan2(ey - cy, ex - cx);
        const degrees = radians * (180 / Math.PI);
        return degrees;
    }
    
    function angleToSalary(angle) {
        // Normalize angle to a 0-360 range
        const normalizedAngle = (angle % 360 + 360) % 360;
        // Map angle to salary (0 to 15000 range)
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
        // Rotate the toggle element based on the angle
        toggle.style.transform = `rotate(${calcAngle}deg)`;
    
        // Update the salary figure
        const salary = angleToSalary(calcAngle);
        salaryInput.textContent = salary;
    
        // Update the loan amount (70% of salary)
        const loan = (salary * 0.7).toFixed(2);
        loanAmount.textContent = `${loan}`;
    }
    
    function onPointerEnter(event) {
        if (event.type === 'touchstart') {
            event.preventDefault(); // Prevent default touch behavior
        }
        toggleBox.addEventListener('mousemove', onPointerMove);
        toggleBox.addEventListener('touchmove', onPointerMove);
    }
    
    function onPointerLeave(event) {
        toggleBox.removeEventListener('mousemove', onPointerMove);
        toggleBox.removeEventListener('touchmove', onPointerMove);
    }
    
    toggleBox.addEventListener('mouseenter', onPointerEnter);
    toggleBox.addEventListener('mouseleave', onPointerLeave);
    toggleBox.addEventListener('touchstart', onPointerEnter);
    toggleBox.addEventListener('touchend', onPointerLeave);
   



// Benefits Section JS Functions
const benefitsBlock = document.querySelector('section.benefits');
const benefitsImages = benefitsBlock.querySelectorAll('.left img');
const benefitsTextBlocks = benefitsBlock.querySelectorAll('.right .benefit');
const benefitsLeftToggle = benefitsBlock.querySelector('.center .left');
const benefitsRightToggle = benefitsBlock.querySelector('.center .right');

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

// Event listeners for manual toggles
benefitsLeftToggle.addEventListener('click', () => {
  benefitsState = (benefitsState - 1 + 3) % 3;
  setBenefitsState();
  resetInterval();  // Reset the interval when manually toggling
});

benefitsRightToggle.addEventListener('click', () => {
  benefitsState = (benefitsState + 1) % 3;
  setBenefitsState();
  resetInterval();  // Reset the interval when manually toggling
});

// Initialize the benefits state and start the auto-slide interval
setBenefitsState();
autoSlideInterval = setInterval(nextSlide, 5000);
