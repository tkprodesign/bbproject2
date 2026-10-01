document.addEventListener('DOMContentLoaded', function() {
    // Hide the preloader after 3 seconds
    setTimeout(function() {
        var preloader = document.querySelector('.preloader');
        preloader.style.display = 'none';
    }, 3000); // 3000 milliseconds = 3 seconds
});





//Toggle Nav
const header = document.querySelector('header');
const mobileNav = document.querySelector('#mobileNav');
const menuToggle = document.querySelector('#menuToggle');
const body = document.querySelector('body');

menuToggle.addEventListener('click', ()=>{
    mobileNav.classList.toggle('active')
    body.classList.toggle('active')
    
})

const headerHeight = header.offsetHeight;
const paddingTop = headerHeight + 100;
mobileNav.style.padding = `${paddingTop}px 5vw 20vh 5vw`;

console.log('we have gotten to this point')

