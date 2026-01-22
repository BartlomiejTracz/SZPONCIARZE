<?php
session_start();

// Sprawdź czy admin jest zalogowany
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

//pobiera filmy z bazy
$query = "SELECT * FROM Filmy ORDER BY rok_wydania DESC";
$stmt = $db->query($query);
$movies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Filmami - Panel Administratora</title>

    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="header-logo-wrapper">
                <a href="index.php" class="logo">Plusflix</a>
                <span class="slogan">Panel Administratora</span>
            </div>

            <div class="header-right">

                <button id="contrastToggle" class="btn btn-secondary btn-sm" title="Tryb wysokiego kontrastu">
                    Kontrast
                </button>

                <div class="theme-toggle" id="themeToggle">
                    <div class="theme-toggle-slider"></div>
                </div>

                <div class="user-info">
                    <div class="user-avatar">AD</div>
                    <span class="user-name">Admin</span>
                </div>

                <a href="admin-logout.php" class="btn btn-secondary btn-sm">
                    Wyloguj
                </a>
            </div>
        </div>
    </div>
</header>

<nav class="admin-nav">
    <div class="container" style="display: flex; gap: var(--spacing-sm);">
        <a href="admin-dashboard.php" class="admin-nav-link">Dashboard</a>
        <a href="admin-movies.php" class="admin-nav-link active">Filmy</a>
        <a href="admin-categories.php" class="admin-nav-link">Kategorie</a>
    </div>
</nav>

<section class="admin-section">
    <div class="container">
        <div class="admin-header">
            <h2 class="admin-title">Zarządzanie filmami</h2>
            <button class="btn btn-primary" onclick="showAddMovieForm()">
                + Dodaj nowy film
            </button>
        </div>
        <!-- Wyszukiwarka -->
        <div class="search-container" style="max-width: 600px; margin-bottom: var(--spacing-xl);">
            <input
                    type="text"
                    class="search-input"
                    id="adminSearchInput"
                    placeholder="Wpisz tytuł filmu..."
            >
        </div>
        <!-- Formularz dodawania/edycji filmu -->
        <div class="admin-form" id="movieForm" style="display: none; margin-bottom: var(--spacing-xl);">
            <h3 style="margin-bottom: var(--spacing-lg); font-family: var(--font-heading);" id="formTitle">
                Dodaj nowy film
            </h3>

            <form method="POST" action="admin-movie-save.php" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="input-group form-grid-full">
                        <label class="input-label" for="movieTitle">Tytuł filmu *</label>
                        <input
                                type="text"
                                class="input"
                                id="movieTitle"
                                name="title"
                                placeholder="np. Joker"
                                required
                        >
                    </div>

                    <div class="input-group">
                        <label class="input-label" for="movieYear">Rok produkcji *</label>
                        <input
                                type="number"
                                class="input"
                                id="movieYear"
                                name="year"
                                placeholder="2019"
                                min="1900"
                                max="2030"
                                required
                        >
                    </div>

                    <div class="input-group">
                        <label class="input-label" for="movieDirector">Reżyseria *</label>
                        <input
                                type="text"
                                class="input"
                                id="movieDirector"
                                name="director"
                                placeholder="Todd Phillips"
                                required
                        >
                    </div>

                    <div class="input-group form-grid-full">
                        <label class="input-label" for="movieDescription">Opis *</label>
                        <textarea
                                class="input"
                                id="movieDescription"
                                name="description"
                                rows="4"
                                placeholder="Wprowadź opis filmu..."
                                required
                        ></textarea>
                    </div>

                    <div class="input-group form-grid-full">
                        <label class="input-label">Kategorie (oddziel ukośnikiem /)</label>
                        <input
                                type="text"
                                class="input"
                                name="genre"
                                placeholder="np. Dramat/Thriller"
                        >
                    </div>

                    <div class="input-group form-grid-full">
                        <label class="input-label">Platformy (oddziel przecinkiem ,)</label>
                        <input
                                type="text"
                                class="input"
                                name="platform"
                                placeholder="np. Netflix, HBO Max"
                        >
                    </div>

                    <div class="input-group form-grid-full">
                        <label class="input-label" for="moviePoster">URL Postera</label>
                        <input
                                type="text"
                                class="input"
                                id="moviePoster"
                                name="poster_url"
                                placeholder="https://fwcdn.pl/..."
                        >
                    </div>
                </div>

                <div style="display: flex; gap: var(--spacing-sm); margin-top: var(--spacing-lg);">
                    <button type="submit" class="btn btn-primary">
                        Zapisz film
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="hideMovieForm()">
                        Anuluj
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela z filmami -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                <tr>
                    <th style="width: 80px;">Poster</th>
                    <th>Tytuł</th>
                    <th style="width: 80px;">Rok</th>
                    <th>Reżyseria</th>
                    <th style="width: 100px;">Ocena</th>
                    <th style="width: 150px;">Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($movies as $movie): ?>
                    <tr>
                        <td>
                            <img
                                    src="<?php echo !empty($movie['poster_url']) ? htmlspecialchars($movie['poster_url']) : 'assets/posters/123.jpg'; ?>"
                                    alt="<?php echo htmlspecialchars($movie['nazwa']); ?>"
                                    class="movie-thumbnail"
                                    onerror="this.src='assets/posters/123.jpg'"
                            >
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($movie['nazwa']); ?></strong><br>
                            <small style="color: var(--text-muted);">
                                <?php echo htmlspecialchars($movie['gatunek']); ?>
                            </small>
                        </td>
                        <td><?php echo $movie['rok_wydania']; ?></td>
                        <td><?php echo htmlspecialchars($movie['rezyser'] ?? ''); ?></td>
                        <td>⭐ <?php echo number_format($movie['srednia_ocena'], 1); ?></td>
                        <td>
                            <div class="table-actions">
                                <button class="btn btn-secondary btn-icon-only"
                                        onclick='editMovie(<?php echo json_encode($movie); ?>)'
                                        title="Edytuj">
                                    ✏️
                                </button>
                                <a href="admin-movie-delete.php?id=<?php echo $movie['id_filmu']; ?>"
                                   class="btn btn-secondary btn-icon-only"
                                   onclick="return confirm('Czy na pewno chcesz usunąć ten film?')"
                                   title="Usuń">
                                    🗑️
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script src="js/theme-switcher.js"></script>
<script src="js/admin-movies.js"></script>
<script>
    // Wyszukiwarka filmów - tylko po tytule (jak na stronie głównej)
    const adminSearchInput = document.getElementById('adminSearchInput');

    if (adminSearchInput) {
        adminSearchInput.addEventListener('input', function() {
            const searchQuery = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('.admin-table tbody tr');

            let visibleCount = 0;

            tableRows.forEach(row => {
                const title = row.querySelector('td:nth-child(2) strong')?.textContent.toLowerCase() || '';
                if (title.includes(searchQuery)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
</script>
</body>
</html>