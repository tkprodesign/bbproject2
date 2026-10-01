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

  const beneficiarySelect=document.getElementById('v3BeneficiarySelect');
  const beneficiaryName=document.getElementById('v3BeneficiaryName');
  const recipientBank=document.getElementById('v3RecipientBank');
  const recipientAccount=document.getElementById('v3RecipientAccount');
  const recipientType=document.getElementById('v3RecipientType');
  const recipientCurrency=document.getElementById('v3RecipientCurrency');
  const fillBeneficiary=()=>{
    if(!beneficiarySelect)return;
    const option=beneficiarySelect.options[beneficiarySelect.selectedIndex];
    if(!option||!option.value)return;
    if(beneficiaryName)beneficiaryName.value=option.dataset.name||'';
    if(recipientBank)recipientBank.value=option.dataset.bank||'';
    if(recipientAccount)recipientAccount.value=option.dataset.account||'';
    if(recipientType&&option.dataset.type)recipientType.value=option.dataset.type;
    if(recipientCurrency&&option.dataset.currency)recipientCurrency.value=option.dataset.currency;
  };
  beneficiarySelect?.addEventListener('change',fillBeneficiary);
  if(beneficiarySelect?.value)fillBeneficiary();
})();