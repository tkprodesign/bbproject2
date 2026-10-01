(function(){
  const sidebar=document.getElementById('v2Sidebar');
  const overlay=document.getElementById('v2SidebarOverlay');
  const button=document.getElementById('v2MenuButton');
  if(!sidebar||!overlay||!button)return;
  const close=()=>{sidebar.classList.remove('open');overlay.classList.remove('show');};
  button.addEventListener('click',()=>{sidebar.classList.toggle('open');overlay.classList.toggle('show');});
  overlay.addEventListener('click',close);
  document.addEventListener('keydown',(event)=>{if(event.key==='Escape')close();});
})();