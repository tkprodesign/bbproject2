// Menu nav toggle behavior
const menuToggle = document.querySelector('#menuToggle');
const nav = document.querySelector('nav');

if (menuToggle && nav) {
  menuToggle.addEventListener('click', () => {
    nav.classList.toggle('active');
    menuToggle.classList.toggle('active');
  });
}

function getGreeting() {
  const hour = new Date().getHours();

  if (hour >= 5 && hour < 12) {
    return 'Good Morning Boss';
  }

  if (hour >= 12 && hour < 17) {
    return 'Good Afternoon Boss';
  }

  return 'Good Evening Boss';
}

const greetingText = document.getElementById('greeting-text');
if (greetingText) {
  greetingText.textContent = getGreeting();
}
