(function(){
  const header=document.getElementById('pv2Header');
  const button=document.getElementById('pv2MenuBtn');
  const nav=document.getElementById('pv2MobileNav');
  if(button&&nav){
    button.addEventListener('click',()=>{
      const open=nav.classList.toggle('open');
      button.setAttribute('aria-expanded',open?'true':'false');
      const icon=button.querySelector('.material-symbols-rounded');
      if(icon) icon.textContent=open?'close':'menu';
    });
    document.addEventListener('click',e=>{
      if(header&&!header.contains(e.target)&&nav.classList.contains('open')){
        nav.classList.remove('open');
        button.setAttribute('aria-expanded','false');
        const icon=button.querySelector('.material-symbols-rounded');
        if(icon) icon.textContent='menu';
      }
    });
  }

  const salary=document.getElementById('loanSalary');
  const tenor=document.getElementById('loanTenor');
  const output=document.getElementById('loanEstimate');
  const apply=document.getElementById('loanApplyLink');
  const calculate=document.getElementById('loanCalculate');
  const money=n=>new Intl.NumberFormat('en-US',{style:'currency',currency:'USD'}).format(n);
  const estimate=()=>{
    if(!salary||!tenor||!output||!apply)return;
    const s=parseFloat(salary.value);
    const t=parseInt(tenor.value,10);
    if(!Number.isFinite(s)||s<=0||![14,30,60].includes(t)){
      output.innerHTML='<span>Enter a monthly salary and tenor to view an illustration.</span>';
      apply.classList.add('disabled');
      apply.removeAttribute('href');
      return;
    }
    const amount=Math.min(s*.4,12000);
    const rate=t===14?.08:(t===30?.12:.18);
    const fee=amount*rate;
    const repayment=amount+fee;
    const installment=(repayment/t)*30;
    output.innerHTML='<div><span>Illustrative amount</span><strong>'+money(amount)+'</strong></div><div><span>Illustrative fee</span><strong>'+money(fee)+'</strong></div><div><span>Total repayment</span><strong>'+money(repayment)+'</strong></div><div><span>Est. monthly equivalent</span><strong>'+money(installment)+'</strong></div>';
    const q=new URLSearchParams({salary:s.toFixed(2),amount:amount.toFixed(2),fee:fee.toFixed(2),tenor:String(t),repayment:repayment.toFixed(2),installment:installment.toFixed(2)});
    apply.href='/loan/?'+q.toString();
    apply.classList.remove('disabled');
  };
  calculate?.addEventListener('click',estimate);
})();