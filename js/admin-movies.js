// Add Movie Form
function showAddMovieForm() {
    const form = document.getElementById('movieForm');
    const formTitle = document.getElementById('formTitle');

    formTitle.textContent = 'Dodaj nowy film';
    form.style.display = 'block';

    form.scrollIntoView({ behavior: 'smooth' });
}

function hideMovieForm() {
    const form = document.getElementById('movieForm');
    form.style.display = 'none';
}

function editMovie(movieId) {
    const form = document.getElementById('movieForm');
    const formTitle = document.getElementById('formTitle');

    formTitle.textContent = 'Edytuj film';
    form.style.display = 'block';

    console.log('Edytuj film ID:', movieId);

    form.scrollIntoView({ behavior: 'smooth' });
}

function deleteMovie(movieId) {
    if (confirm('Czy na pewno chcesz usunąć ten film?')) {
        console.log('Usuń film ID:', movieId);

        alert('Film został usunięty!');

    }
}

const fileInput = document.getElementById('moviePoster');
const fileNameSpan = document.getElementById('fileName');

if (fileInput) {
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            fileNameSpan.textContent = this.files[0].name;
        }
    });
}

const movieFormElement = document.querySelector('#movieForm form');
if (movieFormElement) {
    movieFormElement.addEventListener('submit', function(e) {
        e.preventDefault();

        const title = document.getElementById('movieTitle').value;
        console.log('Dodaj/Edytuj film:', title);

        alert('Film został zapisany!');
        hideMovieForm();

    });
}