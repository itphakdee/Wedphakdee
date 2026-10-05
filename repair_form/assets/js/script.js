// แสดง/ซ่อนรหัสผ่านเมื่อกดไอคอนรูปตา
const pwd = document.getElementById('pwd');
const eye = document.getElementById('toggleEye');

if (pwd && eye) {
  eye.addEventListener('click', () => {
    pwd.type = pwd.type === 'password' ? 'text' : 'password';
  });
}
