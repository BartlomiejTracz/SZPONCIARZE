// Admin Login TODO backup do bazy danych by zapisywaqło i może logowanie z maila prawdziwego
function handleLogin(event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (email === 'admin@szpontowe-seanse.pl' && password === 'admin123') {
        localStorage.setItem('adminLoggedIn', 'true');
        localStorage.setItem('adminEmail', email);

        window.location.href = 'admin-dashboard.html';
    } else {
        alert('Nieprawidłowy email lub hasło!');
    }
}

function checkAdminAuth() {
    const isLoggedIn = localStorage.getItem('adminLoggedIn');

    if (!isLoggedIn) {
        window.location.href = 'admin-login.html';
    }

    return isLoggedIn;
}

function logout() {
    localStorage.removeItem('adminLoggedIn');
    localStorage.removeItem('adminEmail');
    window.location.href = 'index.php';
}