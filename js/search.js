const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');

function filterMovies() {
    const searchQuery = searchInput.value.toLowerCase();
    const selectedCategory = categoryFilter.value;

    const movieCards = document.querySelectorAll('.movie-card');
    let visibleCount = 0;

    movieCards.forEach(card => {
        const title = card.querySelector('.movie-title').textContent.toLowerCase();

        // Pobierz kategorie z data attribute (dodamy to za chwilę)
        const categories = card.dataset.categories || '';

        // Sprawdź czy pasuje do wyszukiwania
        const matchesSearch = title.includes(searchQuery);

        // Sprawdź czy pasuje do kategorii
        const matchesCategory = selectedCategory === '' || categories.includes(selectedCategory);

        // Pokaż tylko jeśli pasuje do obu filtrów
        if (matchesSearch && matchesCategory) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    // Pokaż komunikat jeśli brak wyników
    const moviesGrid = document.getElementById('moviesGrid');
    let emptyMessage = document.getElementById('emptyMessage');

    if (visibleCount === 0) {
        if (!emptyMessage) {
            emptyMessage = document.createElement('div');
            emptyMessage.id = 'emptyMessage';
            emptyMessage.className = 'empty-state';
            emptyMessage.innerHTML = `
                <div class="empty-state-icon">🎬</div>
                <p>Nie znaleziono filmów spełniających kryteria</p>
            `;
            moviesGrid.appendChild(emptyMessage);
        }
        emptyMessage.style.display = 'block';
    } else {
        if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }
    }
}

// Event listeners
searchInput.addEventListener('input', filterMovies);
categoryFilter.addEventListener('change', filterMovies);