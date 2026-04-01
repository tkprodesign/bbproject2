// function updateLastActive() {
//     fetch('update_last_active.php')
//         .then(response => response.text())
//         .then(data => console.log(data))
//         .catch(error => console.error('Error:', error));
// }

// document.addEventListener('DOMContentLoaded', (event) => {
//     setInterval(updateLastActive, 5000); // Update every 5 minutes (300000 milliseconds)
// });





//Close pre header
const preHeader = document.querySelector('.pre-header');
if (preHeader) {
    
    const preHeaderCTA = preHeader.querySelector('.close');
    preHeaderCTA.addEventListener('click', ()=>{
        preHeader.style.display = 'none';
    })
}





//set active cta
document.addEventListener('DOMContentLoaded', function () {
    var currentPath = window.location.pathname;
    var links = document.querySelectorAll('header .container a');
    links.forEach(function (link) {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});






//Header sticky toggle
window.onscroll = function() { toggleStickyHeader() };

    var header = document.querySelector("header");
    var sticky = header.offsetTop;

    function toggleStickyHeader() {
        if (window.pageYOffset > sticky) {
            header.classList.add("sticky");
        } else {
            header.classList.remove("sticky");
        }
    }





//Menu toggle
document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('header nav');
    const menuToggle = document.getElementById('menuToggle');
    const menuToggleChildren = document.querySelectorAll('#menuToggle span');

    menuToggleChildren.forEach(menuchild => {
        menuchild.addEventListener('click', () => {
            console.log('clicked')
            nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });
    });
});