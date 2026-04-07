document.addEventListener("DOMContentLoaded", function() {
// Function to set equal height for all p elements in the same section
function setEqualHeightForParagraphs() {
    let pElements = document.querySelectorAll('section.services .row-1 p');
    let maxHeight = 0;

    // First, reset the height to auto to calculate the natural height
    pElements.forEach(p => {
        p.style.height = 'auto';
    });

    // Calculate the maximum height
    pElements.forEach(p => {
        let height = p.offsetHeight;
        if (height > maxHeight) {
            maxHeight = height;
        }
    });

    // Set all p elements to the maximum height
    pElements.forEach(p => {
        p.style.height = maxHeight + 'px';
    });
}

// Call the function to set equal height for p elements in row-1
setEqualHeightForParagraphs();
});




document.addEventListener("DOMContentLoaded", function() {
// Function to set equal height for all p elements in the same section
function setEqualHeightForParagraphs2() {
    let p2Elements = document.querySelectorAll('section.services .row-2 p');
    let maxHeight = 0;

    // First, reset the height to auto to calculate the natural height
    p2Elements.forEach(p => {
        p.style.height = 'auto';
    });

    // Calculate the maximum height
    p2Elements.forEach(p => {
        let height = p.offsetHeight;
        if (height > maxHeight) {
            maxHeight = height;
        }
    });

    // Set all p elements to the maximum height
    p2Elements.forEach(p => {
        p.style.height = maxHeight + 'px';
    });
}

// Call the function to set equal height for p elements in row-1
setEqualHeightForParagraphs2();
});





document.addEventListener("DOMContentLoaded", function() {
    let isDarkCard = true;
    const darkCard = document.querySelector('.credit-card .dark-card');
    const lightCard = document.querySelector('.credit-card .light-card');
    const placeholderCard = document.querySelector('.credit-card .placeholder');
    const slideInterval = 2000; // 2 seconds
    let intervalId;

    function slideIn() {
        if (isDarkCard) {
            darkCard.style.transform = 'translateX(0)'; // Slide in dark card
            darkCard.style.zIndex = 1;
            darkCard.style.opacity = 1;
            lightCard.style.transform = 'translateX(100%)'; // Hide light card
            lightCard.style.zIndex = 0;
            lightCard.style.opacity = 0;
            placeholderCard.src = "/assets/images/home/credit-card-white.png";
        } else {
            lightCard.style.transform = 'translateX(0)'; // Slide in light card
            lightCard.style.zIndex = 1;
            lightCard.style.opacity = 1;
            darkCard.style.transform = 'translateX(100%)'; // Hide dark card
            darkCard.style.zIndex = 0;
            darkCard.style.opacity = 0;
            placeholderCard.src = "/assets/images/home/credit-card-black.png";
        }
        isDarkCard = !isDarkCard; // Toggle the card to be shown next
    }

    function startSliding() {
        intervalId = setInterval(slideIn, slideInterval);
    }

    function resetInterval() {
        clearInterval(intervalId); // Clear the existing interval
        startSliding(); // Start a new interval
    }

    // Start sliding on button click and reset the interval
    document.getElementById('startButton').addEventListener('click', function() {
        slideIn(); // Perform the slide immediately
        resetInterval(); // Reset the interval
    });
    document.getElementById('startButton2').addEventListener('click', function() {
        slideIn(); // Perform the slide immediately
        resetInterval(); // Reset the interval
    });
    
    // Initial slide in
    slideIn();
    startSliding();
});






document.addEventListener('DOMContentLoaded', function() {
    var statsSection = document.querySelector('.stats');
    var statsSectionInView = false;

    function checkStatsSectionInView() {
        if (!statsSectionInView && isElementInViewport(statsSection)) {
            // Perform your action here
            console.log('Stats section is in viewport!');
            
            // Mark section as viewed
            statsSectionInView = true;
            
            // Animate the numbers
            animateNumbers();
        }
    }

    function isElementInViewport(el) {
        var rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    function animateNumbers() {
        var figures = document.querySelectorAll('.fig');
        figures.forEach(function(fig) {
            var endValue = parseInt(fig.innerText.replace(',', ''));
            var duration = 2000; // Adjust as needed
            var start = 0;
            var step = Math.ceil(endValue / (duration / 60)); // Calculate step based on duration

            var counter = setInterval(function() {
                start += step;
                if (start >= endValue) {
                    clearInterval(counter);
                    start = endValue;
                }
                fig.textContent = numberWithCommas(start);
            }, 1000 / 60); // 60fps
        });
    }

    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Initial check on load
    checkStatsSectionInView();

    // Check on scroll
    window.addEventListener('scroll', checkStatsSectionInView);
});





