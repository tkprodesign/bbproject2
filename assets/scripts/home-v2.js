(function(){
  const header=document.getElementById('hv2Header');
  const button=document.getElementById('hv2MenuBtn');
  const nav=document.getElementById('hv2MobileNav');
  if(button&&nav){
    button.addEventListener('click',()=>{
      const open=nav.classList.toggle('open');
      button.setAttribute('aria-expanded',open?'true':'false');
      const icon=button.querySelector('.material-symbols-rounded');
      if(icon)icon.textContent=open?'close':'menu';
    });
    document.addEventListener('click',e=>{
      if(!header?.contains(e.target)&&nav.classList.contains('open')){
        nav.classList.remove('open');
        button.setAttribute('aria-expanded','false');
        const icon=button.querySelector('.material-symbols-rounded');
        if(icon)icon.textContent='menu';
      }
    });
  }
})();