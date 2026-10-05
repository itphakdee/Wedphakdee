(function(){
  function q(sel,root){return (root||document).querySelector(sel)}
  function qa(sel,root){return Array.prototype.slice.call((root||document).querySelectorAll(sel))}

  qa('[data-multiselect]').forEach(function(box){
    var btn=q('.vehicle-multiselect__button',box), panel=q('.vehicle-multiselect__panel',box), search=q('.vehicle-multiselect__search',box), count=q('.vehicle-selected-count',box);
    if(!btn||!panel)return;
    btn.addEventListener('click',function(e){e.preventDefault();box.classList.toggle('open')});
    document.addEventListener('click',function(e){if(!box.contains(e.target))box.classList.remove('open')});
    function update(){var n=qa('input[type=checkbox]:checked',box).length;if(count)count.textContent=n?'เลือกแล้ว '+n+' คน':'ยังไม่ได้เลือก'}
    qa('input[type=checkbox]',box).forEach(function(cb){cb.addEventListener('change',update)});update();
    if(search){search.addEventListener('input',function(){var term=this.value.toLowerCase();qa('.vehicle-person',box).forEach(function(row){row.style.display=row.textContent.toLowerCase().indexOf(term)>=0?'grid':'none'})})}
  });

  var hospital=q('#vehicle_id'), privateReg=q('#private_registration');
  if(hospital&&privateReg){
    function sync(){if(privateReg.value.trim()!==''){hospital.value='';hospital.disabled=true}else{hospital.disabled=false}}
    privateReg.addEventListener('input',sync);hospital.addEventListener('change',function(){if(this.value){privateReg.value='';privateReg.disabled=true}else{privateReg.disabled=false}});sync();
  }

  qa('[data-confirm]').forEach(function(el){el.addEventListener('click',function(e){if(!confirm(el.getAttribute('data-confirm')))e.preventDefault()})});

  var form=q('#vehicleRequestForm');
  if(form){form.addEventListener('submit',function(e){
    var v=q('#vehicle_id'), p=q('#private_registration');
    if(v&&p&&!v.value&&!p.value.trim()){e.preventDefault();alert('กรุณาเลือกรถโรงพยาบาล หรือกรอกทะเบียนรถยนต์ส่วนตัว');return false}
    var b=q('[type=submit]',form);if(b){b.disabled=true;b.dataset.original=b.innerHTML;b.innerHTML='กำลังบันทึก...'}
  })}
})();
