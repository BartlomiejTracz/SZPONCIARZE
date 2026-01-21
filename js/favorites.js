// Toggle favorite
function toggleFavorite(button) {
    button.classList.toggle('active');
    const heartIcon = button.querySelector('.heart-icon');

    if (button.classList.contains('active')) {
        heartIcon.textContent = '♥';
    } else {
        heartIcon.textContent = '♡';
    }

    // TODO: Zapisz do localStorage
    // const movieId = button.dataset.movieId;
    // saveFavorite(movieId);
}