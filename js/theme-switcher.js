// js/theme-switcher.js
const themeToggle = document.getElementById('themeToggle');
const contrastToggle = document.getElementById('contrastToggle');
const html = document.documentElement;

// Funkcja pomocnicza do pobierania zapisanego motywu (domyślnie dark)
const getSavedTheme = () => localStorage.getItem('theme') || 'dark';

// Inicjalizacja przy starcie strony
const initTheme = () => {
    const saved = getSavedTheme();
    // Jeśli w localStorage jest zapisany kontrast, przywracamy go,
    // ale musimy mieć też zapisany bazowy motyw (light/dark)
    const currentStatus = localStorage.getItem('isContrast') === 'true' ? 'contrast' : saved;
    html.setAttribute('data-theme', currentStatus);

    // Ustawienie pozycji kółka w switchu
    if (saved === 'light' && themeToggle) {
        themeToggle.classList.add('active');
    }
};

// Obsługa zwykłego przełącznika Light/Dark
if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        // Jeśli kontrast jest włączony, najpierw go wyłączamy dla jasności logiki
        const isContrast = html.getAttribute('data-theme') === 'contrast';

        // Pobieramy co było/jest zapisane jako baza
        let baseTheme = getSavedTheme();
        let newTheme = baseTheme === 'light' ? 'dark' : 'light';

        // Zapisujemy nowy wybór jako główny motyw
        localStorage.setItem('theme', newTheme);
        themeToggle.classList.toggle('active');

        // Jeśli NIE jesteśmy w trybie kontrastu, od razu zmieniamy wygląd
        if (!isContrast) {
            html.setAttribute('data-theme', newTheme);
        }
    });
}

// Obsługa trybu Kontrastu
if (contrastToggle) {
    contrastToggle.addEventListener('click', () => {
        const currentTheme = html.getAttribute('data-theme');

        if (currentTheme === 'contrast') {
            // WYŁĄCZANIE KONTRASTU -> Wracamy do tego co jest w localStorage
            const previousTheme = getSavedTheme();
            html.setAttribute('data-theme', previousTheme);
            localStorage.setItem('isContrast', 'false');
        } else {
            // WŁĄCZANIE KONTRASTU
            html.setAttribute('data-theme', 'contrast');
            localStorage.setItem('isContrast', 'true');
        }
    });
}

initTheme();