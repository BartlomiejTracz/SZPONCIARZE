<?php
session_start();

$logout_message = '';
if (isset($_SESSION['logout_message'])) {
    $logout_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

require_once 'config/database.php';

// Nawiązanie połączenia z bazą danych
$database = new Database();
$db = $database->connect();

// 1. POBIERANIE KATEGORII DO FILTRA
$categories = [];
try {
    $checkTable = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='Kategorie'");
    if ($checkTable->fetch()) {
        $stmt = $db->query("SELECT * FROM Kategorie ORDER BY nazwa ASC");
        $categories = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    // Ignorujemy błędy
}

// 2. POBIERANIE FILMÓW
$query = "SELECT * FROM filmy ORDER BY rok_wydania DESC";
$stmt = $db->query($query);
$movies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plusflix</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* Dodatkowy styl inline dla nagłówka sekcji, aby wyrównać przycisk */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: var(--spacing-sm);
        }

        .section-header .section-title {
            margin-bottom: 0;
            border-bottom: none; /* Usuwamy domyślne podkreślenie samego tytułu, bo jest w kontenerze */
            padding-bottom: 0;
        }
    </style>
</head>
<body>
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="header-logo-wrapper">
                <a href="index.php" class="logo">Plusflix</a>
            </div>

            <div class="header-right">
                <button id="contrastToggle" class="btn btn-secondary btn-sm" title="Tryb wysokiego kontrastu">
                    Kontrast
                </button>

                <div class="theme-toggle" id="themeToggle">
                    <div class="theme-toggle-slider"></div>
                </div>

                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <div class="user-info"><div class="user-avatar" id="userAvatar">AD</div><span class="user-name" id="userName">Admin</span><input type="file" id="avatarInput" accept="image/*" hidden></div>
                    <a href="admin-dashboard.php" class="btn btn-secondary btn-sm">Panel Admina</a>
                <?php else: ?>
                    <a href="admin-login.php" class="btn btn-secondary btn-sm">Zaloguj</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="search-container">
    <span class="slogan">Wszystkie filmy w jednym miejscu</span>

    <input
            type="text"
            class="search-input"
            id="searchInput"
            placeholder="Wpisz tytuł filmu lub serialu."
    >

    <select class="category-filter" id="categoryFilter">
        <option value="">Wszystkie kategorie</option>
        <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat['nazwa']); ?>">
                <?php echo htmlspecialchars($cat['nazwa']); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<section class="movies-section">
    <div class="container">

        <div class="section-header">
            <h2 class="section-title">Popularne filmy</h2>

            <a href="favorites.php" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="heart-icon-static" style="color: #e50914;">❤️</span>
                Moje Ulubione
            </a>
        </div>

        <div class="movies-grid" id="moviesGrid">
            <?php foreach ($movies as $movie): ?>
                <a href="movie.php?id=<?php echo $movie['id_filmu']; ?>"
                   class="movie-card"
                   data-categories="<?php echo htmlspecialchars($movie['gatunek']); ?>">
                    <div style="position: relative;">
                        <img
                                src="<?php echo !empty($movie['poster_url']) ? htmlspecialchars($movie['poster_url']) : 'assets/posters/123.jpg'; ?>"
                                alt="<?php echo htmlspecialchars($movie['nazwa']); ?>"
                                class="movie-poster"
                        >
                        <button class="favorite-btn" onclick="event.preventDefault(); toggleFavorite(this, <?php echo $movie['id_filmu']; ?>)">
                            <span class="heart-icon">♡</span>
                        </button>
                    </div>
                    <div class="movie-info">
                        <h3 class="movie-title"><?php echo htmlspecialchars($movie['nazwa']); ?></h3>
                        <div class="movie-meta">
                            <span class="movie-year"><?php echo $movie['rok_wydania']; ?></span>
                            <div class="movie-rating">
                                <span class="star-icon">★</span>
                                <span><?php echo number_format($movie['srednia_ocena'], 1); ?></span>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php if (empty($movies)): ?>
                <p style="grid-column: 1/-1; text-align: center; padding: 2rem;">Brak filmów w bazie.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="js/theme-switcher.js"></script>
<script src="js/search.js"></script>
<script src="js/favorites.js"></script>
<script src="js/admin-user-info.js"></script>

<script>
    <?php if (!empty($logout_message)): ?>
    alert('✅ <?php echo addslashes($logout_message); ?>');
    <?php endif; ?>
</script>
</body>
</html>