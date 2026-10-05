document.addEventListener('DOMContentLoaded', function () {
  var equipment = document.getElementById('equipment_id');
  if (equipment) {
    equipment.addEventListener('change', function () {
      var opt = this.options[this.selectedIndex];
      if (!opt || !opt.value) return;
      var map = {asset_code:'asset', equipment_name:'name', category:'category', brand:'brand', model:'model', serial_no:'serial'};
      Object.keys(map).forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.value = opt.dataset[map[id]] || '';
      });
      var loc = document.querySelector('[name="location"]');
      if (loc && !loc.value && opt.dataset.location) loc.value = opt.dataset.location;
    });
  }
});
