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

// Pobierz statystyki
$stats = [];

// Liczba filmów
$query = "SELECT COUNT(*) as total FROM Filmy";
$stmt = $db->query($query);
$stats['movies'] = $stmt->fetch()['total'];

// Liczba recenzji
$query = "SELECT COUNT(*) as total FROM Recenzje";
$stmt = $db->query($query);
$stats['reviews'] = $stmt->fetch()['total'];

// Liczba unikalnych gatunków
$query = "SELECT COUNT(DISTINCT gatunek) as total FROM Filmy";
$stmt = $db->query($query);
$stats['categories'] = $stmt->fetch()['total'];

// Ostatnio dodane filmy (top 5)
$query = "SELECT f.*, COUNT(r.id_recenzji) as review_count 
          FROM Filmy f 
          LEFT JOIN Recenzje r ON f.id_filmu = r.id_filmu 
          GROUP BY f.id_filmu 
          ORDER BY f.rok_wydania DESC";
$stmt = $db->query($query);
$recent_movies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Panel Administratora</title>

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
                <a href="index.php" class="logo">Szpontowe Seanse</a>
                <span class="slogan">Panel Administratora</span>
            </div>

            <div class="header-right">
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
        <a href="admin-dashboard.php" class="admin-nav-link active">Dashboard</a>
        <a href="admin-movies.php" class="admin-nav-link">Filmy</a>
        <a href="admin-categories.php" class="admin-nav-link">Kategorie</a>
    </div>
</nav>

<section class="admin-section">
    <div class="container">
        <!-- Statystyki -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Wszystkie filmy</div>
                <div class="stat-value"><?php echo number_format($stats['movies']); ?></div>
                <div class="stat-icon">🎬</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Recenzje</div>
                <div class="stat-value"><?php echo number_format($stats['reviews']); ?></div>
                <div class="stat-icon">💬</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Kategorie</div>
                <div class="stat-value"><?php echo $stats['categories']; ?></div>
                <div class="stat-icon">📁</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Średnia ocen</div>
                <div class="stat-value">
                    <?php
                    $query = "SELECT AVG(srednia_ocena) as avg_rating FROM Filmy";
                    $stmt = $db->query($query);
                    $avg = $stmt->fetch()['avg_rating'];
                    echo number_format($avg, 1);
                    ?>
                </div>
                <div class="stat-icon">⭐</div>
            </div>
        </div>

        <div class="admin-header">
            <h2 class="admin-title">Ostatnio dodane filmy</h2>
            <a href="admin-movies.php" class="btn btn-primary">
                + Dodaj film
            </a>
        </div>

        <!-- Tabela ostatnich filmów -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                <tr>
                    <th style="width: 80px;">Poster</th>
                    <th>Tytuł</th>
                    <th style="width: 80px;">Rok</th>
                    <th style="width: 100px;">Ocena</th>
                    <th style="width: 100px;">Recenzje</th>
                    <th style="width: 150px;">Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($recent_movies as $movie): ?>
                    <tr>
                        <td>
                            <img
                                    src="<?php echo !empty($movie['poster_url']) ? htmlspecialchars($movie['poster_url']) : 'assets/posters/123.jpg'; ?>"
                                    alt="<?php echo htmlspecialchars($movie['nazwa']); ?>"
                                    class="movie-thumbnail"
                                    onerror="this.src='assets/posters/123.jpg'"
                            >
                        </td>
                        <td><strong><?php echo htmlspecialchars($movie['nazwa']); ?></strong></td>
                        <td><?php echo $movie['rok_wydania']; ?></td>
                        <td>⭐ <?php echo number_format($movie['srednia_ocena'], 1); ?></td>
                        <td><?php echo $movie['review_count']; ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="admin-movies.php" class="btn btn-secondary btn-icon-only" title="Edytuj">
                                    ✏️
                                </a>
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

                <?php if (empty($recent_movies)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: var(--spacing-xl); color: var(--text-muted);">
                            Brak filmów w bazie danych
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script src="js/theme-switcher.js"></script>
</body>
</html>