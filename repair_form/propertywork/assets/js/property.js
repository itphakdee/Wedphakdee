document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.delete-form').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      var asset = form.getAttribute('data-asset') || '';
      if (!window.confirm('ยืนยันลบครุภัณฑ์ ' + asset + ' ?\nการลบไม่สามารถย้อนกลับได้')) {
        event.preventDefault();
      }
    });
  });


  // Pagination fallback: บังคับเปลี่ยนหน้าเมื่อกดลิงก์ ถ้ามี CSS/สคริปต์อื่นในระบบรบกวน event ของ anchor
  document.querySelectorAll('[data-pagination] a[data-page-link="1"]').forEach(function (link) {
    link.addEventListener('click', function (event) {
      var href = link.getAttribute('href');
      if (!href || href === '#' || link.classList.contains('disabled')) {
        event.preventDefault();
        return;
      }

      // ป้องกัน script ภายนอก intercept ลิงก์ pagination แล้วค้างหน้าเดิม
      event.preventDefault();
      event.stopPropagation();
      window.location.assign(href);
    }, true);
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
