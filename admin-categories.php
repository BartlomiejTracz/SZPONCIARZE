<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once 'config/database.php';

$database = new Database();
$db = $database->connect();

$query = "SELECT gatunek, COUNT(*) as count FROM Filmy GROUP BY gatunek ORDER BY count DESC";
$stmt = $db->query($query);
$categories_raw = $stmt->fetchAll();

$categories = [];
foreach ($categories_raw as $cat) {
$genres = explode('/', $cat['gatunek']);
foreach ($genres as $genre) {
$genre = trim($genre);
if (!isset($categories[$genre])) {
$categories[$genre] = 0;
}
$categories[$genre] += $cat['count'];
}
}

$category_icons = [
'Dramat' => '🎭',
'Komedia' => '😂',
'Thriller' => '🕵️',
'Horror' => '😱',
'Sci-Fi' => '🚀',
'Akcja' => '💥',
'Romans' => '❤️',
'Animacja' => '🎨',
'Biograficzny' => '🏆',
'Kryminał' => '🔪',
'Wojenny' => '⚔️',
'Post-apo' => '☢️',
'Dokument' => '📹'
];

$category_colors = [
'Dramat' => '#e50914',
'Komedia' => '#ffd700',
'Thriller' => '#8b00ff',
'Horror' => '#000000',
'Sci-Fi' => '#00bfff',
'Akcja' => '#ff4500',
'Romans' => '#ff1493',
'Animacja' => '#32cd32',
'Biograficzny' => '#daa520',
'Kryminał' => '#8b0000',
'Wojenny' => '#556b2f',
'Post-apo' => '#2f4f4f',
'Dokument' => '#4682b4'
];
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zarządzanie Kategoriami - Panel Administratora</title>

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
    <a href="admin-movies.php" class="admin-nav-link">Filmy</a>
    <a href="admin-categories.php" class="admin-nav-link active">Kategorie</a>
  </div>
</nav>

<section class="admin-section">
  <div class="container">
    <div class="admin-header">
      <h2 class="admin-title">Zarządzanie kategoriami</h2>
      <p style="color: var(--text-secondary); margin-top: var(--spacing-xs);">
        Kategorie są automatycznie generowane z gatunków filmów
      </p>
    </div>

    <!-- Grid kategorii -->
    <div class="categories-grid">
      <?php foreach ($categories as $name => $count): ?>
      <div class="category-card">
        <div class="category-header">
          <div class="category-icon">
            <?php echo isset($category_icons[$name]) ? $category_icons[$name] : '🎬'; ?>
          </div>
          <div class="category-color-badge" style="background-color: <?php echo isset($category_colors[$name]) ? $category_colors[$name] : '#e50914'; ?>;"></div>
        </div>
        <h3 class="category-name"><?php echo htmlspecialchars($name); ?></h3>
        <p class="category-slug"><?php echo strtolower(str_replace(' ', '-', $name)); ?></p>
        <p class="category-description">
          Kategoria <?php echo htmlspecialchars($name); ?>
        </p>
        <div class="category-stats">
          <span>🎬 <?php echo $count; ?> <?php echo $count == 1 ? 'film' : ($count < 5 ? 'filmy' : 'filmów'); ?></span>
        </div>
        <div class="category-actions">
          <a href="index.php" class="btn btn-secondary btn-sm">
            👁️ Zobacz filmy
          </a>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if (empty($categories)): ?>
      <div style="grid-column: 1/-1; text-align: center; padding: var(--spacing-xl); color: var(--text-muted);">
        <p style="font-size: 1.25rem;">Brak kategorii</p>
        <p>Dodaj filmy z gatunkami, aby kategorie się pojawiły</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<script src="js/theme-switcher.js"></script>
</body>
</html>