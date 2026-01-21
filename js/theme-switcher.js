// Theme Switcher
const themeToggle = document.getElementById('themeToggle');
const htmlElement = document.documentElement;

// Sprawdź zapisany motyw w localStorage
const currentTheme = localStorage.getItem('theme') || 'dark';
htmlElement.setAttribute('data-theme', currentTheme);

// Ustaw toggle na podstawie zapisanego motywu
if (currentTheme === 'light') {
    themeToggle.classList.add('active');
}

// Obsługa kliknięcia
themeToggle.addEventListener('click', () => {
    const currentTheme = htmlElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

    // Zmień motyw
    htmlElement.setAttribute('data-theme', newTheme);

    // Zapisz w localStorage
    localStorage.setItem('theme', newTheme);

    // Animacja toggle
    themeToggle.classList.toggle('active');
});