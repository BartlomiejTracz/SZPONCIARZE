const userAvatar = document.getElementById('userAvatar');
const avatarInput = document.getElementById('avatarInput');

if (userAvatar && avatarInput) {
    const savedAvatar = localStorage.getItem('admin_avatar');
    if (savedAvatar) {
        userAvatar.style.backgroundImage = `url(${savedAvatar})`;
        userAvatar.textContent = '';
    }
    userAvatar.addEventListener('click', () => {
        avatarInput.click();
    });
    avatarInput.addEventListener('change', () => {
        const file = avatarInput.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Proszę wybrać obraz');
            return;
        }
        const reader = new FileReader();
        reader.onload = () => {
            const base64 = reader.result;
            localStorage.setItem('admin_avatar', base64);

            userAvatar.style.backgroundImage = `url(${base64})`;
            userAvatar.textContent = '';
        };
        reader.readAsDataURL(file);
    });
}

const userName = document.getElementById('userName');
if (userName) {
    const savedName = localStorage.getItem('admin_name');
    if (savedName) {
        userName.textContent = savedName;
    }
    userName.addEventListener('click', () => {
        const oldValue = userName.textContent;
        const input = document.createElement('input');
        input.type = 'text';
        input.value = oldValue;
        input.className = 'input';
        input.style.width = '120px';

        userName.replaceWith(input);
        input.focus();

        function save() {
            const newValue = input.value.trim() || oldValue;
            localStorage.setItem('admin_name', newValue);

            userName.textContent = newValue;
            input.replaceWith(userName);
        }

        input.addEventListener('blur', save);
        input.addEventListener('keydown', e => {
            if (e.key === 'Enter') save();
            if (e.key === 'Escape') input.replaceWith(userName);
        });
    });
}