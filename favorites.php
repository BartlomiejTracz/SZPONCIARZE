<?php
session_start();
require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

// Pobieramy ID z adresu URL (np. favorites.php?ids=1,2,3)
$ids_raw = isset($_GET['ids']) ? $_GET['ids'] : '';
$movies = [];

if (!empty($ids_raw)) {
    $ids_array = explode(',', $ids_raw);
    $ids_array = array_map('intval', $ids_array); // Zabezpieczenie przed SQL Injection

    if (!empty($ids_array)) {
        $placeholders = implode(',', array_fill(0, count($ids_array), '?'));
        // Sprawdź czy tabela to filmy czy Filmy - używamy Filmy zgodnie z movie.php
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
</head>
<body>
<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo">Plusflix</a>
            <div class="header-right">
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

<script src="js/favorites.js"></script>
<script>
    // Magia: Jeśli w URL nie ma parametrów, pobierz je z LocalStorage i przeładuj stronę
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('ids')) {
            const favs = JSON.parse(localStorage.getItem('plusflix_favorites') || '[]');
            if (favs.length > 0) {
                window.location.href = 'favorites.php?ids=' + favs.join(',');
            } else {
                document.getElementById('emptyMsg').innerHTML = "<p>Nie masz żadnych ulubionych filmów. ❤️</p>";
            }
        }
    });

    function removeFromFavs(id) {
        let favs = JSON.parse(localStorage.getItem('plusflix_favorites') || '[]');
        favs = favs.filter(f => f !== id);
        localStorage.setItem('plusflix_favorites', JSON.stringify(favs));
        // Przeładuj stronę z nową listą ID
        window.location.href = 'favorites.php?ids=' + favs.join(',');
    }
</script>
</body>
</html>