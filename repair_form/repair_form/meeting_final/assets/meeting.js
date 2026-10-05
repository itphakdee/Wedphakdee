(function(){
  'use strict';

  function byId(id){ return document.getElementById(id); }

  var room=byId('room_id');
  var attendees=byId('attendees');
  var roomInfo=byId('roomInfo');
  var capacityMessage=byId('capacityMessage');

  function updateCapacity(){
    if(!room || !attendees) return;
    var option=room.options[room.selectedIndex];
    var capacity=parseInt(option && option.getAttribute('data-capacity') || '0',10);
    var location=option && option.getAttribute('data-location') || '';
    var count=parseInt(attendees.value || '0',10);

    if(roomInfo){
      roomInfo.textContent=capacity ? ('รองรับสูงสุด '+capacity+' คน'+(location?' · '+location:'')) : 'ระบบจะแสดงความจุเมื่อเลือกห้อง';
    }
    if(capacityMessage){
      if(!capacity){ capacityMessage.textContent=''; }
      else if(count>capacity){
        capacityMessage.textContent='จำนวนผู้เข้าร่วมเกินความจุห้อง ('+capacity+' คน)';
        capacityMessage.style.color='#b53541';
      }else{
        capacityMessage.textContent='รองรับได้ เหลือ '+Math.max(0,capacity-count)+' ที่นั่ง';
        capacityMessage.style.color='#0e7859';
      }
    }
  }

  if(room) room.addEventListener('change',updateCapacity);
  if(attendees) attendees.addEventListener('input',updateCapacity);
  updateCapacity();

  var bookingForm=byId('bookingForm');
  if(bookingForm){
    bookingForm.addEventListener('submit',function(e){
      var start=bookingForm.querySelector('[name="start_time"]');
      var end=bookingForm.querySelector('[name="end_time"]');
      if(start && end && start.value && end.value && start.value>=end.value){
        e.preventDefault();
        alert('เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม');
        end.focus();
        return;
      }
      if(room && attendees){
        var option=room.options[room.selectedIndex];
        var cap=parseInt(option && option.getAttribute('data-capacity') || '0',10);
        if(cap && parseInt(attendees.value||'0',10)>cap){
          e.preventDefault();
          alert('จำนวนผู้เข้าร่วมเกินความจุของห้องประชุม');
          attendees.focus();
          return;
        }
      }
    });
  }
})();