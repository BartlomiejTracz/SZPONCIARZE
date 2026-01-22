<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

// ==========================================
// 1. AUTOMATYCZNA MIGRACJA (Tworzenie tabeli Kategorie)
// ==========================================
$checkTable = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='Kategorie'");
if (!$checkTable->fetch()) {
    $db->exec("CREATE TABLE Kategorie (id_kategorii INTEGER PRIMARY KEY AUTOINCREMENT, nazwa TEXT NOT NULL UNIQUE)");

    // Pobierz unikalne gatunki z filmów
    $stmt = $db->query("SELECT gatunek FROM Filmy");
    $movies = $stmt->fetchAll();
    $unique_cats = [];

    foreach ($movies as $m) {
        if (!empty($m['gatunek'])) {
            $parts = explode('/', $m['gatunek']);
            foreach ($parts as $p) {
                $unique_cats[trim($p)] = true;
            }
        }
    }

    $insert = $db->prepare("INSERT OR IGNORE INTO Kategorie (nazwa) VALUES (:nazwa)");
    foreach (array_keys($unique_cats) as $catName) {
        $insert->execute([':nazwa' => $catName]);
    }
}

// ==========================================
// 2. LOGIKA CRUD (Dodawanie, Edycja, Usuwanie)
// ==========================================

// DODAWANIE / EDYCJA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_category') {
        $name = trim($_POST['cat_name']);
        $id = isset($_POST['cat_id']) ? $_POST['cat_id'] : '';

        if (!empty($name)) {
            if (!empty($id)) {
                // Edycja
                $stmt = $db->prepare("UPDATE Kategorie SET nazwa = :nazwa WHERE id_kategorii = :id");
                $stmt->bindParam(':id', $id);
            } else {
                // Dodawanie
                $stmt = $db->prepare("INSERT OR IGNORE INTO Kategorie (nazwa) VALUES (:nazwa)");
            }
            $stmt->bindParam(':nazwa', $name);
            $stmt->execute();
        }
    }
    header("Location: admin-categories.php");
    exit;
}

// USUWANIE
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    // Usuwanie z bindowaniem parametru
    $stmt = $db->prepare("DELETE FROM Kategorie WHERE id_kategorii = :id");
    $stmt->execute([':id' => $id]);

    header("Location: admin-categories.php");
    exit;
}

// ==========================================
// 3. POBIERANIE DANYCH (BEZ FILTROWANIA SQL)
// ==========================================
// Pobieramy wszystko, filtrowanie robi JS
$sql = "SELECT * FROM Kategorie ORDER BY nazwa ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$categories = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zarządzanie Kategoriami</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        .categories-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .categories-table th, .categories-table td { padding: 1rem; border-bottom: 1px solid var(--border-color); text-align: left; }
        .categories-table th { background: var(--bg-secondary); }
        .action-btn-group { display: flex; gap: 0.5rem; }

        /* Styl dla inputa wyszukiwania, żeby był na całą szerokość */
        .search-full-width {
            width: 100%;
            padding: 12px;
            margin-bottom: 32px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: var(--radius-md);
            box-sizing: border-box;
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
                <div class="user-info"><div class="user-avatar">AD</div><span class="user-name">Admin</span></div>
                <a href="admin-logout.php" class="btn btn-secondary btn-sm">Wyloguj</a>
            </div>
        </div>
    </div>
</header>

<nav class="admin-nav">
    <div class="container" style="display: flex; gap: var(--spacing-sm);">
        <a href="admin-dashboard.php" class="admin-nav-link">Dashboard</a>
        <a href="admin-categories.php" class="admin-nav-link active">Kategorie</a>
    </div>
</nav>

<section class="admin-section">
    <div class="container">
        <div class="admin-header">
            <h2 class="admin-title">Zarządzanie kategoriami</h2>
            <button class="btn btn-primary" onclick="showCategoryForm()">+ Dodaj kategorię</button>
        </div>

        <div id="categoryFormContainer" class="admin-form" style="display:none; margin-bottom: 2rem;">
            <h3 id="formTitle">Dodaj nową kategorię</h3>
            <form method="POST" action="admin-categories.php">
                <input type="hidden" name="action" value="save_category">
                <input type="hidden" name="cat_id" id="catId">
                <div class="input-group">
                    <label class="input-label">Nazwa kategorii</label>
                    <input type="text" name="cat_name" id="catName" class="input" required placeholder="np. Sci-Fi">
                </div>
                <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Zapisz</button>
                    <button type="button" class="btn btn-secondary" onclick="hideCategoryForm()">Anuluj</button>
                </div>
            </form>
        </div>

            <input type="text"
                   id="categorySearchInput"
                   class="search-input search-full-width"
                   placeholder="Wpisz nazwę kategorii aby wyszukać..."/>

        <div class="admin-table-container">
            <table class="categories-table">
                <thead>
                <tr>
                    <th>Nazwa kategorii</th>
                    <th style="width: 150px;">Akcje</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cat['nazwa']); ?></td>
                        <td>
                            <div class="action-btn-group">
                                <button class="btn btn-secondary btn-sm" onclick='editCategory(<?php echo json_encode($cat); ?>)'>Edytuj</button>
                                <a href="admin-categories.php?delete_id=<?php echo $cat['id_kategorii']; ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Czy na pewno usunąć tę kategorię?')" style="color: #e50914; border-color: #e50914;">Usuń</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categories)): ?>
                    <tr><td colspan="2" style="text-align:center; padding: 2rem;">Brak kategorii. Dodaj pierwszą!</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</section>

<script src="js/theme-switcher.js"></script>

<script>
    // --- WYSZUKIWARKA JS (Skopiowana i dostosowana z dashboardu) ---
    const searchInput = document.getElementById('categorySearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            // Szukamy w wierszach tabeli kategorii
            document.querySelectorAll('.categories-table tbody tr').forEach(row => {
                const txt = row.innerText.toLowerCase();
                if (txt.includes(val)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // --- OBSŁUGA FORMULARZA ---
    function showCategoryForm() {
        document.getElementById('categoryFormContainer').style.display = 'block';
        document.getElementById('formTitle').innerText = 'Dodaj nową kategorię';
        document.getElementById('catId').value = '';
        document.getElementById('catName').value = '';
        document.getElementById('catName').focus();
    }

    function editCategory(cat) {
        document.getElementById('categoryFormContainer').style.display = 'block';
        document.getElementById('formTitle').innerText = 'Edytuj kategorię';
        document.getElementById('catId').value = cat.id_kategorii;
        document.getElementById('catName').value = cat.nazwa;
        document.getElementById('catName').focus();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function hideCategoryForm() {
        document.getElementById('categoryFormContainer').style.display = 'none';
    }
</script>

</body>
</html>