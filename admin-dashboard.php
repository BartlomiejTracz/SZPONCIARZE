<?php
session_start();

// Sprawdzenie uprawnień
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

// ==========================================
// KONFIGURACJA: LISTA PLATFORM
// ==========================================
$availablePlatforms = [
        'Netflix',
        'HBO Max',
        'Disney+',
        'Amazon Prime Video',
        'SkyShowtime',
        'Apple TV+',
        'Viaplay',
        'Canal+ Online',
        'CDA Premium',
        'Kino'
];

// ==========================================
// 0. AUTO-MIGRACJA (Kategorie)
// ==========================================
$checkTable = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='Kategorie'");
if (!$checkTable->fetch()) {
    $db->exec("CREATE TABLE Kategorie (id_kategorii INTEGER PRIMARY KEY AUTOINCREMENT, nazwa TEXT NOT NULL UNIQUE)");
    $stmt = $db->query("SELECT gatunek FROM Filmy");
    $all_genres = [];
    foreach ($stmt->fetchAll() as $row) {
        if (!empty($row['gatunek'])) {
            $parts = explode('/', $row['gatunek']);
            foreach ($parts as $p) $all_genres[trim($p)] = true;
        }
    }
    $ins = $db->prepare("INSERT OR IGNORE INTO Kategorie (nazwa) VALUES (:n)");
    foreach (array_keys($all_genres) as $g) $ins->execute([':n' => $g]);
}

// ==========================================
// 1. LOGIKA ZAPISU
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'save_movie') {

    $id_filmu = $_POST['id_filmu'];
    $nazwa = trim($_POST['title']);
    $rok = (int) $_POST['year'];
    $rezyser = trim($_POST['director']);
    $opis = trim($_POST['description']);
    $poster_url = trim($_POST['poster_url']);

    // 1. Przetwarzanie KATEGORII
    $gatunek_string = '';
    if (isset($_POST['genres']) && is_array($_POST['genres'])) {
        $gatunek_string = implode('/', $_POST['genres']);
    }

    // 2. Przetwarzanie PLATFORM
    $platforma_string = '';
    if (isset($_POST['platforms']) && is_array($_POST['platforms'])) {
        $platforma_string = implode(', ', $_POST['platforms']);
    }

    if (empty($nazwa) || empty($rok)) {
        $_SESSION['message'] = "Błąd: Tytuł i rok są wymagane!";
        $_SESSION['message_type'] = "error";
    } else {
        try {
            if (!empty($id_filmu)) {
                // UPDATE
                $sql = "UPDATE Filmy SET 
                        nazwa = :nazwa, 
                        rok_wydania = :rok, 
                        rezyser = :rezyser, 
                        opis = :opis, 
                        gatunek = :gatunek, 
                        platforma = :platforma, 
                        poster_url = :poster 
                        WHERE id_filmu = :id";

                $stmt = $db->prepare($sql);
                $stmt->bindParam(':id', $id_filmu);
                $msg = "Zaktualizowano film.";
            } else {
                // INSERT
                $sql = "INSERT INTO Filmy (nazwa, rok_wydania, rezyser, opis, gatunek, platforma, poster_url, srednia_ocena) 
                        VALUES (:nazwa, :rok, :rezyser, :opis, :gatunek, :platforma, :poster, 0)";

                $stmt = $db->prepare($sql);
                $msg = "Dodano nowy film.";
            }

            $stmt->bindParam(':nazwa', $nazwa);
            $stmt->bindParam(':rok', $rok);
            $stmt->bindParam(':rezyser', $rezyser);
            $stmt->bindParam(':opis', $opis);
            $stmt->bindParam(':gatunek', $gatunek_string);
            $stmt->bindParam(':platforma', $platforma_string);
            $stmt->bindParam(':poster', $poster_url);

            if ($stmt->execute()) {
                $_SESSION['message'] = $msg;
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Błąd bazy danych.";
                $_SESSION['message_type'] = "error";
            }

        } catch (PDOException $e) {
            $_SESSION['message'] = "Wyjątek SQL: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
        }
    }

    header("Location: admin-dashboard.php");
    exit;
}

// Obsługa usuwania
if (isset($_GET['delete_id'])) {
    $delId = (int) $_GET['delete_id'];
    $db->prepare("DELETE FROM Recenzje WHERE id_filmu = ?")->execute([$delId]);
    $db->prepare("DELETE FROM Filmy WHERE id_filmu = ?")->execute([$delId]);
    $_SESSION['message'] = "Usunięto film.";
    $_SESSION['message_type'] = "success";
    header("Location: admin-dashboard.php");
    exit;
}

// ==========================================
// 2. POBIERANIE DANYCH
// ==========================================
$stats = [
        'movies' => $db->query("SELECT COUNT(*) FROM Filmy")->fetchColumn(),
        'reviews' => $db->query("SELECT COUNT(*) FROM Recenzje")->fetchColumn(),
        'categories' => $db->query("SELECT COUNT(*) FROM Kategorie")->fetchColumn(),
        'avg' => number_format((float)$db->query("SELECT AVG(srednia_ocena) FROM Filmy")->fetchColumn(), 1)
];

$allCategories = $db->query("SELECT * FROM Kategorie ORDER BY nazwa ASC")->fetchAll();

$movies = $db->query("SELECT f.*, f.platforma as platformy, COUNT(r.id_recenzji) as review_count 
                      FROM Filmy f 
                      LEFT JOIN Recenzje r ON f.id_filmu = r.id_filmu 
                      GROUP BY f.id_filmu 
                      ORDER BY f.id_filmu DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>Panel Administratora</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .genres-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 8px;
            background: var(--bg-tertiary);
            padding: 10px;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
        }
        .genre-checkbox {
            display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.9rem;
        }
        .genre-checkbox input { accent-color: var(--accent-primary); width: 16px; height: 16px; }

        /* Styl dla grupy przycisków akcji (taki sam jak w admin-categories.php) */
        .action-btn-group {
            display: flex;
            gap: 0.5rem;
        }
    </style>
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
                <button id="contrastToggle" class="btn btn-secondary btn-sm">Kontrast</button>
                <div class="theme-toggle" id="themeToggle"><div class="theme-toggle-slider"></div></div>
                <div class="user-info"><div class="user-avatar" id="userAvatar">AD</div><span class="user-name" id="userName">Admin</span><input type="file" id="avatarInput" accept="image/*" hidden></div>
                <a href="admin-logout.php" class="btn btn-secondary btn-sm">Wyloguj</a>
            </div>
        </div>
    </div>
</header>

<nav class="admin-nav">
    <div class="container" style="display: flex; gap: var(--spacing-sm);">
        <a href="admin-dashboard.php" class="admin-nav-link active">Dashboard</a>
        <a href="admin-categories.php" class="admin-nav-link">Kategorie</a>
    </div>
</nav>

<section class="admin-section">
    <div class="container">

        <?php if (isset($_SESSION['message'])): ?>
            <div style="padding: 1rem; margin-bottom: 1rem; border: 1px solid currentColor;
                    color: <?php echo $_SESSION['message_type'] == 'success' ? '#32cd32' : '#e50914'; ?>;">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card"><div class="stat-label">Filmy</div><div class="stat-value"><?php echo $stats['movies']; ?></div><div class="stat-icon">🎬</div></div>
            <div class="stat-card"><div class="stat-label">Recenzje</div><div class="stat-value"><?php echo $stats['reviews']; ?></div><div class="stat-icon">💬</div></div>
            <div class="stat-card"><div class="stat-label">Kategorie</div><div class="stat-value"><?php echo $stats['categories']; ?></div><div class="stat-icon">📁</div></div>
            <div class="stat-card"><div class="stat-label">Średnia</div><div class="stat-value"><?php echo $stats['avg']; ?></div><div class="stat-icon">⭐</div></div>
        </div>

        <div class="admin-header">
            <h2 class="admin-title">Zarządzanie filmami</h2>
            <button class="btn btn-primary" onclick="showAddMovieForm()">+ Dodaj nowy film</button>
        </div>

        <div class="admin-form" id="movieForm" style="display: none; margin-bottom: 2rem;">
            <h3 id="formTitle">Dodaj nowy film</h3>
            <form method="POST" action="admin-dashboard.php" id="actualForm">
                <input type="hidden" name="form_action" value="save_movie">
                <input type="hidden" name="id_filmu" id="movieId">

                <div class="form-grid">
                    <div class="input-group form-grid-full">
                        <label class="input-label">Tytuł filmu *</label>
                        <input type="text" class="input" id="movieTitle" name="title" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Rok *</label>
                        <input type="number" class="input" id="movieYear" name="year" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Reżyser *</label>
                        <input type="text" class="input" id="movieDirector" name="director" required>
                    </div>
                    <div class="input-group form-grid-full">
                        <label class="input-label">Opis *</label>
                        <textarea class="input" id="movieDescription" name="description" rows="3" required></textarea>
                    </div>

                    <div class="input-group form-grid-full">
                        <label class="input-label">Kategorie</label>
                        <div class="genres-grid">
                            <?php foreach($allCategories as $cat): ?>
                                <label class="genre-checkbox">
                                    <input type="checkbox" name="genres[]" value="<?php echo htmlspecialchars($cat['nazwa']); ?>" class="genre-cb">
                                    <?php echo htmlspecialchars($cat['nazwa']); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="input-group form-grid-full">
                        <label class="input-label">Platformy</label>
                        <div class="genres-grid">
                            <?php foreach($availablePlatforms as $plat): ?>
                                <label class="genre-checkbox">
                                    <input type="checkbox" name="platforms[]" value="<?php echo htmlspecialchars($plat); ?>" class="platform-cb">
                                    <?php echo htmlspecialchars($plat); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="input-group form-grid-full">
                        <label class="input-label">URL Postera</label>
                        <input type="text" class="input" id="moviePoster" name="poster_url">
                    </div>
                </div>
                <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" id="submitBtn">Zapisz film</button>
                    <button type="button" class="btn btn-secondary" onclick="hideMovieForm()">Anuluj</button>
                </div>
            </form>
        </div>

        <input type="text" id="adminSearchInput" class="search-input" placeholder="Wyszukaj film..." style="width: 100%; padding: 12px; margin-bottom: 1rem; border: 1px solid var(--border-color); background: var(--bg-secondary); color: var(--text-primary); border-radius: var(--radius-md);">

        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                <tr>
                    <th width="60">Img</th>
                    <th>Tytuł</th>
                    <th>Rok</th>
                    <th>Kategorie</th>
                    <th style="width: 150px;">Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($movies as $movie): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($movie['poster_url'] ?: 'assets/posters/123.jpg'); ?>" class="movie-thumbnail" style="width: 40px; height: 60px; object-fit: cover;"></td>
                        <td>
                            <strong><?php echo htmlspecialchars($movie['nazwa']); ?></strong>
                        </td>
                        <td><?php echo $movie['rok_wydania']; ?></td>
                        <td><small><?php echo htmlspecialchars($movie['gatunek']); ?></small></td>
                        <td>
                            <div class="action-btn-group">
                                <button class="btn btn-secondary btn-sm" onclick='editMovie(<?php echo json_encode($movie); ?>)'>Edytuj</button>
                                <a href="admin-dashboard.php?delete_id=<?php echo $movie['id_filmu']; ?>"
                                   class="btn btn-secondary btn-sm"
                                   onclick="return confirm('Usunąć?')"
                                   style="color: #e50914; border-color: #e50914;">
                                    Usuń
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
<script src="js/admin-user-info.js"></script>
<script>
    const searchInput = document.getElementById('adminSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            document.querySelectorAll('.admin-table tbody tr').forEach(row => {
                const txt = row.innerText.toLowerCase();
                row.style.display = txt.includes(val) ? '' : 'none';
            });
        });
    }

    function showAddMovieForm() {
        document.getElementById('actualForm').reset();
        document.getElementById('movieId').value = '';
        document.getElementById('formTitle').innerText = "Dodaj nowy film";
        document.getElementById('submitBtn').innerText = "Zapisz film";

        // Reset checkboxów
        document.querySelectorAll('.genre-cb').forEach(cb => cb.checked = false);
        document.querySelectorAll('.platform-cb').forEach(cb => cb.checked = false);

        const f = document.getElementById('movieForm');
        f.style.display = 'block';
        f.scrollIntoView({ behavior: 'smooth' });
    }

    function editMovie(movie) {
        document.getElementById('movieId').value = movie.id_filmu;
        document.getElementById('movieTitle').value = movie.nazwa;
        document.getElementById('movieYear').value = movie.rok_wydania;
        document.getElementById('movieDirector').value = movie.rezyser || '';
        document.getElementById('movieDescription').value = movie.opis;
        document.getElementById('moviePoster').value = movie.poster_url;

        // 1. Zaznaczanie KATEGORII (separator: /)
        document.querySelectorAll('.genre-cb').forEach(cb => cb.checked = false);
        if (movie.gatunek) {
            const genres = movie.gatunek.split('/');
            document.querySelectorAll('.genre-cb').forEach(cb => {
                if (genres.some(g => g.trim() === cb.value.trim())) cb.checked = true;
            });
        }

        // 2. Zaznaczanie PLATFORM (separator: przecinek)
        document.querySelectorAll('.platform-cb').forEach(cb => cb.checked = false);
        if (movie.platformy) {
            const platforms = movie.platformy.split(',');
            document.querySelectorAll('.platform-cb').forEach(cb => {
                if (platforms.some(p => p.trim() === cb.value.trim())) cb.checked = true;
            });
        }

        document.getElementById('formTitle').innerText = "Edytuj film: " + movie.nazwa;
        document.getElementById('submitBtn').innerText = "Zaktualizuj film";

        const f = document.getElementById('movieForm');
        f.style.display = 'block';
        f.scrollIntoView({ behavior: 'smooth' });
    }

    function hideMovieForm() {
        document.getElementById('movieForm').style.display = 'none';
    }
</script>
</body>
</html>