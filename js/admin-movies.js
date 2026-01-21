// Show Add Movie Form
function showAddMovieForm() {
    const form = document.getElementById('movieForm');
    const formTitle = document.getElementById('formTitle');

    formTitle.textContent = 'Dodaj nowy film';
    form.style.display = 'block';

    // Scroll to form
    form.scrollIntoView({ behavior: 'smooth' });
}

// Hide Movie Form
function hideMovieForm() {
    const form = document.getElementById('movieForm');
    form.style.display = 'none';
}

// Edit Movie
function editMovie(movieId) {
    const form = document.getElementById('movieForm');
    const formTitle = document.getElementById('formTitle');

    formTitle.textContent = 'Edytuj film';
    form.style.display = 'block';

    // TODO: Załaduj dane filmu o ID: movieId
    console.log('Edytuj film ID:', movieId);

    // Scroll to form
    form.scrollIntoView({ behavior: 'smooth' });
}

// Delete Movie
function deleteMovie(movieId) {
    if (confirm('Czy na pewno chcesz usunąć ten film?')) {
        // TODO: Wyślij żądanie usunięcia do backendu
        console.log('Usuń film ID:', movieId);

        alert('Film został usunięty!');

        // Refresh strony (mockup)
        // location.reload();
    }
}

// File input - show filename
const fileInput = document.getElementById('moviePoster');
const fileNameSpan = document.getElementById('fileName');

if (fileInput) {
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            fileNameSpan.textContent = this.files[0].name;
        }
    });
}

// Form submit handler (mockup)
const movieFormElement = document.querySelector('#movieForm form');
if (movieFormElement) {
    movieFormElement.addEventListener('submit', function(e) {
        e.preventDefault();

        const title = document.getElementById('movieTitle').value;
        console.log('Dodaj/Edytuj film:', title);

        alert('Film został zapisany!');
        hideMovieForm();

        // TODO: Wyślij dane do backendu
    });
}