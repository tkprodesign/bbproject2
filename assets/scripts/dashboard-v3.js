(function(){
  const sidebar=document.getElementById('v3Sidebar');
  const overlay=document.getElementById('v3Overlay');
  const menu=document.getElementById('v3Menu');
  const close=()=>{sidebar?.classList.remove('open');overlay?.classList.remove('show');};
  menu?.addEventListener('click',()=>{sidebar?.classList.toggle('open');overlay?.classList.toggle('show');});
  overlay?.addEventListener('click',close);
  document.addEventListener('keydown',e=>{if(e.key==='Escape')close();});

  const search=document.getElementById('v3TxSearch');
  const table=document.getElementById('v3TxTable');
  const empty=document.getElementById('v3TxEmpty');
  const filterButtons=[...document.querySelectorAll('[data-v3-filter]')];
  let filter='all';
  const apply=()=>{
    if(!table)return;
    const q=(search?.value||'').trim().toLowerCase();
    let visible=0;
    table.querySelectorAll('tbody tr').forEach(row=>{
      const kind=row.dataset.kind||'';
      const status=row.dataset.status||'';
      const matchesFilter=filter==='all'||filter===kind||filter===status;
      const matchesSearch=!q||row.textContent.toLowerCase().includes(q);
      const show=matchesFilter&&matchesSearch;
      row.style.display=show?'':'none';
      if(show)visible++;
    });
    if(empty)empty.style.display=visible?'none':'block';
  };
  search?.addEventListener('input',apply);
  filterButtons.forEach(btn=>btn.addEventListener('click',()=>{
    filter=btn.dataset.v3Filter||'all';
    filterButtons.forEach(b=>b.classList.toggle('active',b===btn));
    apply();
  }));
})();