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