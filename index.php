<?php
session_start();

$logout_message = '';
if (isset($_SESSION['logout_message'])) {
    $logout_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

require_once 'config/database.php';
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
</head>
<body>
<!-- HEADER -->
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

                <!-- Logowanie admina -->
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
                    <a href="admin-dashboard.php" class="btn btn-secondary btn-sm">Panel Admina</a>
                <?php else: ?>
                    <a href="admin-login.php" class="btn btn-secondary btn-sm">Zaloguj</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- Search -->
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
        <option value="Sci-Fi">Sci-Fi</option>
        <option value="Biograficzny">Biograficzny</option>
        <option value="Kryminał">Kryminał</option>
        <option value="Dramat">Dramat</option>
        <option value="Wojenny">Wojenny</option>
        <option value="Thriller">Thriller</option>
        <option value="Akcja">Akcja</option>
        <option value="Romans">Romans</option>
        <option value="Horror">Horror</option>
        <option value="Post-apo">Post-apo</option>
        <option value="Dokument">Dokument</option>
    </select>
</div>

<div class="search-actions-bar">
    <div class="container">
        <div class="actions-wrapper">
            <a href="favorites.php" class="favorites-link">
                <span class="heart-icon-static">❤️</span>
                Moje Ulubione
            </a>
        </div>
    </div>
</div>

<!-- Movies -->
<section class="movies-section">
  <div class="container">
    <h2 class="section-title">Popularne filmy</h2>

      <div class="movies-grid" id="moviesGrid">
          <!-- baza danych połączenie -->
          <?php
          error_reporting(E_ALL);
          ini_set('display_errors', 1);

          require_once 'config/database.php';

          $database = new Database();
          $db = $database->connect();

          echo "<!-- DEBUG: Połączenie z bazą OK -->";

          $query = "SELECT * FROM filmy ORDER BY rok_wydania DESC";
          $stmt = $db->query($query);
          $movies = $stmt->fetchAll();

          echo "<!-- DEBUG: Liczba filmów: " . count($movies) . " -->";

          foreach ($movies as $movie):
              ?>
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
      </div>
  </div>
</section>

<script src="js/theme-switcher.js"></script>
<script src="js/search.js"></script>
<script src="js/favorites.js"></script>

<script>
    <?php if (!empty($logout_message)): ?>
    alert('✅ <?php echo addslashes($logout_message); ?>');
    <?php endif; ?>
</script>
</body>
</html>