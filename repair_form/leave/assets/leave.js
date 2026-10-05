(function(){
  function q(s,r){return (r||document).querySelector(s)}
  function qa(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s))}
  var s=q('[data-leave-start]'), e=q('[data-leave-end]'), d=q('[data-leave-days]');
  function days(){if(!s||!e||!d||!s.value||!e.value)return;var a=new Date(s.value+'T00:00:00'),b=new Date(e.value+'T00:00:00');if(b<a){d.value='';return}d.value=Math.floor((b-a)/86400000)+1}
  if(s&&e){s.addEventListener('change',days);e.addEventListener('change',days);days()}
  qa('[data-confirm]').forEach(function(el){el.addEventListener('click',function(ev){var m=el.getAttribute('data-confirm')||'ยืนยันการทำรายการ?';if(!window.confirm(m))ev.preventDefault()})});
  var dept=q('[data-dept-filter]'); if(dept){dept.addEventListener('change',function(){dept.form.submit()})}
})();
