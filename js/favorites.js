/*************************
 *  COOKIES – POMOCNICZE
 *************************/
const FAVORITES_COOKIE = 'plusflix_favorites';
const COOKIE_DAYS = 365;

function getCookie(name) {
    const cookies = document.cookie.split(';');
    for (let c of cookies) {
        c = c.trim();
        if (c.startsWith(name + '=')) {
            return decodeURIComponent(c.substring(name.length + 1));
        }
    }
    return null;
}

function setCookie(name, value, days) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${encodeURIComponent(value)};expires=${date.toUTCString()};path=/`;
}

function removeFromFavs(id) {
    let favs = JSON.parse(getCookie('plusflix_favorites') || '[]');
    favs = favs.filter(f => f !== id);
    setCookie('plusflix_favorites', JSON.stringify(favs), 365);
    location.reload();
}

/*************************
 *  FAVORITES – LOGIKA
 *************************/
function getFavorites() {
    const favs = getCookie(FAVORITES_COOKIE);
    return favs ? JSON.parse(favs) : [];
}

function saveFavorites(favs) {
    setCookie(FAVORITES_COOKIE, JSON.stringify(favs), COOKIE_DAYS);
}

/*************************
 *  TOGGLE FAVORITE
 *************************/
function toggleFavorite(button, movieId) {
    let favs = getFavorites();
    const index = favs.indexOf(movieId);

    if (index === -1) {
        // dodaj do ulubionych
        favs.push(movieId);
        button.classList.add('active');
        button.querySelector('.heart-icon').innerText = '❤️';
    } else {
        // usuń z ulubionych
        favs.splice(index, 1);
        button.classList.remove('active');
        button.querySelector('.heart-icon').innerText = '♡';
    }

    saveFavorites(favs);
}

/*************************
 *  INIT FAVORITES
 *************************/
function initFavorites() {
    const favs = getFavorites();
    const buttons = document.querySelectorAll('.favorite-btn, .favorite-btn-large');

    buttons.forEach(btn => {
        const onclickAttr = btn.getAttribute('onclick');
        const match = onclickAttr?.match(/toggleFavorite\(this,\s*(\d+)\)/);

        if (match && match[1]) {
            const movieId = parseInt(match[1], 10);

            if (favs.includes(movieId)) {
                btn.classList.add('active');

                const icon = btn.querySelector('.heart-icon');
                if (icon) icon.innerText = '❤️';

                const textSpan = btn.querySelector('.favorite-text');
                if (textSpan) textSpan.innerText = 'Ulubiony';
            }
        }
    });
}

/*************************
 *  START
 *************************/
document.addEventListener('DOMContentLoaded', initFavorites);
