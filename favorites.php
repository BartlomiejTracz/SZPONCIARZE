<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

$movies = [];

if (isset($_COOKIE['plusflix_favorites'])) {
    $ids_array = json_decode($_COOKIE['plusflix_favorites'], true);

    if (is_array($ids_array) && !empty($ids_array)) {
        $ids_array = array_map('intval', $ids_array);
        $placeholders = implode(',', array_fill(0, count($ids_array), '?'));

        $query = "SELECT * FROM Filmy WHERE id_filmu IN ($placeholders)";
        $stmt = $db->prepare($query);
        $stmt->execute($ids_array);
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Moje Ulubione - Plusflix</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>
<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo">Plusflix</a>
            <div class="header-right">
                <button id="contrastToggle" class="btn btn-secondary btn-sm" title="Tryb wysokiego kontrastu">
                    Kontrast
                </button>

                <div class="theme-toggle" id="themeToggle">
                    <div class="theme-toggle-slider"></div>
                </div>

                <a href="index.php" class="btn btn-primary btn-sm">Powrót</a>
            </div>
        </div>
    </div>
</header>

<section class="movies-section">
    <div class="container">
        <h2 class="section-title">Twoja Kolekcja</h2>
        <div class="movies-grid" id="favoritesGrid">
            <?php if (empty($movies)): ?>
                <div class="empty-state" id="emptyMsg">
                    <p>Ładowanie Twojej kolekcji...</p>
                </div>
            <?php else: ?>
                <?php foreach ($movies as $movie): ?>
                    <a href="movie.php?id=<?php echo $movie['id_filmu']; ?>" class="movie-card">
                        <div style="position: relative;">
                            <img src="<?php echo htmlspecialchars($movie['poster_url'] ?? 'assets/posters/123.jpg'); ?>" class="movie-poster">
                            <button class="favorite-btn active" onclick="event.preventDefault(); removeFromFavs(<?php echo $movie['id_filmu']; ?>)">
                                <span class="heart-icon">❤️</span>
                            </button>
                        </div>
                        <div class="movie-info">
                            <h3 class="movie-title"><?php echo htmlspecialchars($movie['nazwa'] ?? ''); ?></h3>
                            <div class="movie-meta">
                                <span><?php echo $movie['rok_wydania']; ?></span>
                                <span>★ <?php echo number_format($movie['srednia_ocena'], 1); ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="js/theme-switcher.js"></script>
<script src="js/favorites.js"></script>
<script>
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
        date.setTime(date.getTime() + (days*24*60*60*1000));
        document.cookie = `${name}=${encodeURIComponent(value)};expires=${date.toUTCString()};path=/`;
    }

    // --- live remove favorites ---
    document.addEventListener('DOMContentLoaded', () => {
        const grid = document.getElementById('favoritesGrid');
        const favs = JSON.parse(getCookie('plusflix_favorites') || '[]');

        // якщо немає улюблених — показуємо порожнє повідомлення
        if (favs.length === 0 && grid) {
            grid.innerHTML = "<div class='empty-state'><p>Nie masz żadnych ulubionych filmów ❤️</p></div>";
        }

        // додаємо обробку кнопок ❤️
        const buttons = document.querySelectorAll('.favorite-btn');
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const movieId = parseInt(btn.dataset.movieId, 10);
                let favs = JSON.parse(getCookie('plusflix_favorites') || '[]');

                // видаляємо з cookie
                favs = favs.filter(f => f !== movieId);
                setCookie('plusflix_favorites', JSON.stringify(favs), 365);

                // видаляємо картку з DOM
                const card = btn.closest('.movie-card');
                if (card) card.remove();

                // якщо більше немає улюблених — показуємо повідомлення
                if (!grid.querySelector('.movie-card')) {
                    grid.innerHTML = "<div class='empty-state'><p>Nie masz żadnych ulubionych filmów ❤️</p></div>";
                }
            });
        });
    });
</script>
</body>
</html>