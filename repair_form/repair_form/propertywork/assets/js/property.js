document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.delete-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var asset = form.getAttribute('data-asset') || '';
      if (!window.confirm('ยืนยันลบครุภัณฑ์ ' + asset + ' ?\nการลบไม่สามารถย้อนกลับได้')) {
        event.preventDefault();
      }
    });
  });

  var imageInput = document.getElementById('image');
  var preview = document.getElementById('imagePreview');
  if (imageInput && preview) {
    imageInput.addEventListener('change', function () {
      var file = this.files && this.files[0];
      if (!file) { return; }
      var reader = new FileReader();
      reader.onload = function (e) {
        preview.src = e.target.result;
        preview.classList.add('visible');
      };
      reader.readAsDataURL(file);
    });
  }

  var price = document.getElementById('price');
  if (price) {
    price.addEventListener('blur', function () {
      var value = String(this.value).replace(/,/g, '');
      if (value !== '' && !isNaN(value)) {
        this.value = Number(value).toFixed(2);
      }
    });
  }
});
