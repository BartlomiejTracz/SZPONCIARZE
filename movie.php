<?php
require_once 'config/database.php';

// Pobierz ID filmu z URL
$movie_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$database = new Database();
$db = $database->connect();

// Pobierz dane filmu
$query = "SELECT * FROM Filmy WHERE id_filmu = :id";
$stmt = $db->prepare($query);
$stmt->execute(['id' => $movie_id]);
$movie = $stmt->fetch();

if (!$movie) {
    header('Location: index.php');
    exit;
}

// Pobierz recenzje filmu
$query_reviews = "SELECT * FROM Recenzje WHERE id_filmu = :id ORDER BY data_dodania DESC";
$stmt_reviews = $db->prepare($query_reviews);
$stmt_reviews->execute(['id' => $movie_id]);
$reviews = $stmt_reviews->fetchAll();

// Policz liczbę recenzji
$review_count = count($reviews);

// Pobierz aktorów filmu
$query_actors = "
    SELECT a.imie, a.nazwisko 
    FROM Aktorzy a
    JOIN Film_Aktor fa ON a.id_aktora = fa.id_aktora
    WHERE fa.id_filmu = :id
";
$stmt_actors = $db->prepare($query_actors);
$stmt_actors->execute(['id' => $movie_id]);
$actors = $stmt_actors->fetchAll();

$actors_list = array_map(function($actor) {
    return $actor['imie'] . ' ' . $actor['nazwisko'];
}, $actors);
$actors_string = implode(', ', $actors_list);
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($movie['nazwa']); ?> - Szpontowe Seanse</title>

    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/movie-details.css">
</head>
<body>
<!-- HEADER -->
<header class="header">
    <div class="container">
        <div class="header-content">
            <div class="header-logo-wrapper">
                <a href="index.php" class="logo">Szpontowe Seanse</a>
                <span class="slogan">Twoje filmy, wszędzie</span>
            </div>

            <div class="header-right">
                <div class="theme-toggle" id="themeToggle">
                    <div class="theme-toggle-slider"></div>
                </div>

                <a href="admin-login.html" class="btn btn-secondary btn-sm">Panel Admin</a>
            </div>
        </div>
    </div>
</header>

<!-- MOVIE DETAILS -->
<section class="movie-details-section">
    <div class="container">
        <div class="movie-details-grid">
            <!-- Poster -->
            <div class="movie-poster-large-wrapper">
                <img
                        src="<?php echo !empty($movie['poster_url']) ? htmlspecialchars($movie['poster_url']) : 'assets/posters/123.jpg'; ?>"
                        alt="<?php echo htmlspecialchars($movie['nazwa']); ?>"
                        class="movie-poster-large"
                        onerror="this.src='assets/posters/123.jpg'"
                >
                <button class="favorite-btn-large" onclick="toggleFavorite(this, <?php echo $movie['id_filmu']; ?>)">
                    <span class="heart-icon">♡</span>
                    <span class="favorite-text">Dodaj do ulubionych</span>
                </button>
            </div>

            <!-- Info -->
            <div class="movie-info-detailed">
                <!-- BADGES NAD TYTUŁEM -->
                <div class="movie-badges">
                    <?php
                    $genres = explode('/', $movie['gatunek']);
                    foreach ($genres as $genre):
                        ?>
                        <span class="badge badge-primary"><?php echo trim(htmlspecialchars($genre)); ?></span>
                    <?php endforeach; ?>
                </div>

                <!-- TYTUŁ I GWIAZDKI -->
                <div class="movie-header-wrapper">
                    <h1 class="movie-title-large"><?php echo htmlspecialchars($movie['nazwa']); ?></h1>

                    <!-- GWIAZDKI -->
                    <div class="rating-box-inline">
                        <h3>Oceń film</h3>
                        <div class="rating-stars-inline" id="ratingStars">
                            <span class="rating-star" data-rating="1">★</span>
                            <span class="rating-star" data-rating="2">★</span>
                            <span class="rating-star" data-rating="3">★</span>
                            <span class="rating-star" data-rating="4">★</span>
                            <span class="rating-star" data-rating="5">★</span>
                        </div>
                        <p class="rating-message" id="ratingMessage"></p>
                    </div>
                </div>

                <!-- META INFO -->
                <div class="movie-meta-large">
                    <div class="meta-item">
                        <span class="meta-label">Rok produkcji:</span>
                        <span class="meta-value"><?php echo $movie['rok_wydania']; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Reżyseria:</span>
                        <span class="meta-value"><?php echo htmlspecialchars($movie['rezyser']); ?></span>
                    </div>
                    <?php if (!empty($actors_string)): ?>
                        <div class="meta-item">
                            <span class="meta-label">Obsada:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($actors_string); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="meta-item">
                        <span class="meta-label">Ocena:</span>
                        <div class="movie-rating-large">
                            <span class="star-icon">★</span>
                            <span class="rating-value"><?php echo number_format($movie['srednia_ocena'], 1); ?></span>
                            <span class="rating-count">(<?php echo $review_count; ?> <?php echo $review_count == 1 ? 'ocena' : ($review_count < 5 ? 'oceny' : 'ocen'); ?>)</span>
                        </div>
                    </div>
                </div>

                <!-- OPIS -->
                <div class="movie-description">
                    <h3>Opis</h3>
                    <p><?php echo nl2br(htmlspecialchars($movie['opis'])); ?></p>
                </div>

                <!-- PLATFORMY -->
                <div class="streaming-platforms">
                    <h3>Dostępne na platformach:</h3>
                    <div class="platforms-list">
                        <?php
                        $platforms = explode(',', $movie['platforma']);
                        foreach ($platforms as $platform):
                            ?>
                            <span class="platform-badge"><?php echo trim(htmlspecialchars($platform)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- REVIEWS SECTION -->
<section class="reviews-section">
    <div class="container">
        <h2 class="section-title">Recenzje użytkowników (<?php echo $review_count; ?>)</h2>

        <!-- Add Review Form -->
        <div class="add-review-box">
            <h3>Dodaj swoją recenzję</h3>
            <textarea
                    class="review-textarea"
                    id="reviewTextarea"
                    placeholder="Podziel się swoją opinią o filmie..."
                    rows="5"
            ></textarea>
            <button class="btn btn-primary" onclick="addReview()">
                <span>Opublikuj recenzję</span>
            </button>
        </div>

        <!-- Reviews List -->
        <div class="reviews-list" id="reviewsList">
            <?php if (count($reviews) > 0): ?>
                <?php foreach ($reviews as $review): ?>
                    <!-- Review -->
                    <div class="review-card">
                        <div class="review-header">
                            <div class="review-author">
                                <div class="author-avatar">U</div>
                                <div>
                                    <div class="author-name">Użytkownik</div>
                                    <div class="review-date">
                                        <?php
                                        $date = new DateTime($review['data_dodania']);
                                        echo $date->format('d.m.Y H:i');
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="review-rating">
                                <span class="star-icon">★</span>
                                <span><?php echo $review['ocena_gwiazdki']; ?>/5</span>
                            </div>
                        </div>
                        <div class="review-content">
                            <?php echo nl2br(htmlspecialchars($review['tresc_recenzji'])); ?>
                        </div>
                        <div class="review-actions">
                            <button class="review-action-btn">👍 Pomocne (0)</button>
                            <button class="review-action-btn delete-review" onclick="deleteReview(<?php echo $review['id_recenzji']; ?>)" title="Usuń recenzję (tylko administrator)">
                                🗑️ Usuń
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="reviews-empty">
                    <div class="reviews-empty-icon">💬</div>
                    <p>Brak recenzji. Bądź pierwszą osobą, która doda recenzję!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="js/theme-switcher.js"></script>
<script src="js/favorites.js"></script>
<script src="js/movie-rating.js"></script>
<script src="js/reviews.js"></script>
</body>
</html>