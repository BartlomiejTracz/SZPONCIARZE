// Admin Login Handler
function handleLogin(event) {
    event.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    // Mockup - sprawdzenie danych logowania
    if (email === 'admin@szpontowe-seanse.pl' && password === 'admin123') {
        // Zapisz sesję admina
        localStorage.setItem('adminLoggedIn', 'true');
        localStorage.setItem('adminEmail', email);

        // Przekieruj do dashboardu
        window.location.href = 'admin-dashboard.html';
    } else {
        alert('Nieprawidłowy email lub hasło!');
    }
}

// Sprawdź czy admin jest zalogowany (dla innych stron)
function checkAdminAuth() {
    const isLoggedIn = localStorage.getItem('adminLoggedIn');

    if (!isLoggedIn) {
        window.location.href = 'admin-login.html';
    }

    return isLoggedIn;
}

// Wylogowanie
function logout() {
    localStorage.removeItem('adminLoggedIn');
    localStorage.removeItem('adminEmail');
    window.location.href = 'index.php';
}