// Toggle favorite TODO trzeba zrobić backend narazie tylko popkazuje ikonkę
function toggleFavorite(button) {
    button.classList.toggle('active');
    const heartIcon = button.querySelector('.heart-icon');

    if (button.classList.contains('active')) {
        heartIcon.textContent = '♥';
    } else {
        heartIcon.textContent = '♡';
    }

}

// Funkcja pobierająca listę ulubionych z pamięci przeglądarki
function getFavorites() {
    const favs = localStorage.getItem('plusflix_favorites');
    return favs ? JSON.parse(favs) : [];
}

// Funkcja zapisująca listę
function saveFavorites(favs) {
    localStorage.setItem('plusflix_favorites', JSON.stringify(favs));
}

function toggleFavorite(button, movieId) {
    let favs = getFavorites();
    const index = favs.indexOf(movieId);

    if (index === -1) {
        // Dodaj do ulubionych
        favs.push(movieId);
        button.classList.add('active');
        button.querySelector('.heart-icon').innerText = '❤️'; // Zmiana na pełne serce
    } else {
        // Usuń z ulubionych
        favs.splice(index, 1);
        button.classList.remove('active');
        button.querySelector('.heart-icon').innerText = '♡'; // Powrót do pustego
    }

    saveFavorites(favs);
}

// Funkcja, która przy starcie strony sprawdzi, które filmy są ulubione i zapali serca
function initFavorites() {
    const favs = getFavorites();
    const buttons = document.querySelectorAll('.favorite-btn, .favorite-btn-large');

    buttons.forEach(btn => {
        // Pobieramy ID filmu z atrybutu przekazanego w funkcji onclick (parsujemy string)
        const onclickAttr = btn.getAttribute('onclick');
        const match = onclickAttr.match(/toggleFavorite\(this,\s*(\d+)\)/);

        if (match && match[1]) {
            const id = parseInt(match[1]);
            if (favs.includes(id)) {
                btn.classList.add('active');
                const icon = btn.querySelector('.heart-icon');
                if (icon) icon.innerText = '❤️';

                // Jeśli to duży przycisk na stronie movie.php, zmień tekst
                const textSpan = btn.querySelector('.favorite-text');
                if (textSpan) textSpan.innerText = 'Ulubiony';
            }
        }
    });
}

// Uruchom przy załadowaniu strony
document.addEventListener('DOMContentLoaded', initFavorites);