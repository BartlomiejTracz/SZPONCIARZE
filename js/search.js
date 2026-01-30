//Search backend
const searchInput = document.getElementById('searchInput');
const categoryFilter = document.getElementById('categoryFilter');
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const searchVal = params.get('search') || '';
    const categoryVal = params.get('category') || '';

    searchInput.value = searchVal;
    categoryFilter.value = categoryVal;

    filterMovies(); // застосовуємо фільтр одразу
});

function filterMovies() {
    const searchQuery = searchInput.value.toLowerCase().trim();
    const selectedCategory = categoryFilter.value;

    const movieCards = document.querySelectorAll('.movie-card');
    let visibleCount = 0;

    movieCards.forEach(card => {
        const title = card.querySelector('.movie-title')?.textContent.toLowerCase() || '';

        const director = card.dataset.director || '';
        const year = card.dataset.year || '';
        const actors = card.dataset.actors || '';
        const platforms = card.dataset.platforms || '';
        const categories = card.dataset.categories || '';
        const categoriesArray = categories.split(',').map(c => c.trim().toLowerCase());

        const searchableText = `
            ${title}
            ${director}
            ${year}
            ${actors}
            ${platforms}
        `;

        const matchesSearch =
            searchQuery === '' || searchableText.includes(searchQuery);

        const matchesCategory =
            selectedCategory === '' || categoriesArray.includes(selectedCategory.toLowerCase());


        if (matchesSearch && matchesCategory) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

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

    const params = new URLSearchParams(window.location.search);
    if (searchQuery) params.set('search', searchQuery); else params.delete('search');
    if (selectedCategory) params.set('category', selectedCategory); else params.delete('category');
    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState({}, '', newUrl);
}


searchInput.addEventListener('input', filterMovies);
categoryFilter.addEventListener('change', filterMovies);