// Add Movie Form
function editMovie(movie) {
    // 1. Pokaż formularz
    const form = document.getElementById('movieForm');
    form.style.display = 'block';

    // 2. Zmień nagłówek formularza
    document.getElementById('formTitle').innerText = 'Edytuj film: ' + movie.nazwa;

    // 3. Wypełnij pola danymi z obiektu 'movie'
    // Upewnij się, że nazwy pól (name) zgadzają się z tymi w formularzu
    document.getElementById('movieTitle').value = movie.nazwa;
    document.getElementById('movieYear').value = movie.rok_wydania;
    document.getElementById('movieDirector').value = movie.rezyser;
    document.getElementById('movieDescription').value = movie.opis;

    // Pola gatunku i platformy (używamy querySelector dla name, jeśli nie mają ID)
    document.querySelector('input[name="genre"]').value = movie.gatunek;
    document.querySelector('input[name="platform"]').value = movie.platforma;
    document.getElementById('moviePoster').value = movie.poster_url;

    // 4. Przesuń widok do formularza, żeby admin widział co edytuje
    form.scrollIntoView({ behavior: 'smooth' });

    // 5. DODATKOWO: Musimy przekazać ID filmu do skryptu zapisującego
    // Najlepiej dodać ukryte pole w formularzu HTML:
    let hiddenId = document.getElementById('movieEditId');
    if (!hiddenId) {
        hiddenId = document.createElement('input');
        hiddenId.type = 'hidden';
        hiddenId.id = 'movieEditId';
        hiddenId.name = 'id_filmu';
        form.querySelector('form').appendChild(hiddenId);
    }
    hiddenId.value = movie.id_filmu;
}

function showAddMovieForm() {
    // Resetowanie formularza przy dodawaniu nowego
    const form = document.getElementById('movieForm');
    form.querySelector('form').reset();
    document.getElementById('formTitle').innerText = 'Dodaj nowy film';

    // Usuwamy ukryte ID jeśli istnieje
    const hiddenId = document.getElementById('movieEditId');
    if (hiddenId) hiddenId.remove();

    form.style.display = 'block';
}

function hideMovieForm() {
    document.getElementById('movieForm').style.display = 'none';
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