<?php
session_start();
$logout_message = '';
if (isset($_SESSION['logout_message'])) {
    $logout_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}
// dashboard jeśli jesteś zalogowany
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin-dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Sprawdź dane logowania (demo credentials)
    if ($email === 'admin@szpontowe-seanse.pl' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        header('Location: admin-dashboard.php');
        exit;
    } else {
        $error = 'Nieprawidłowy email lub hasło!';
    }
}
?>
<!DOCTYPE html>
<html lang="pl" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Administratora - Plusflix</title>

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
      </div>

      <div class="header-right">

          <button id="contrastToggle" class="btn btn-secondary btn-sm" title="Tryb wysokiego kontrastu">
              Kontrast
          </button>

        <div class="theme-toggle" id="themeToggle">
          <div class="theme-toggle-slider"></div>
        </div>
      </div>
    </div>
  </div>
</header>

<section class="admin-login-section">
  <div class="container">
    <div class="login-container">
      <div class="login-box">
        <h1 class="login-title">Panel Administratora</h1>
        <p class="login-subtitle">Zaloguj się, aby zarządzać filmami</p>

        <?php if ($error): ?>
        <div class="error-message" style="background-color: var(--accent-primary); color: #fff; padding: var(--spacing-md); border-radius: var(--radius-md); margin-bottom: var(--spacing-md); text-align: center;">
          <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form class="login-form" method="POST" action="admin-login.php">
          <div class="input-group">
            <label class="input-label" for="email">Email</label>
            <input
                    type="email"
                    class="input"
                    id="email"
                    name="email"
                    placeholder="admin@szpontowe-seanse.pl"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
            >
          </div>

          <div class="input-group">
            <label class="input-label" for="password">Hasło</label>
            <input
                    type="password"
                    class="input"
                    id="password"
                    name="password"
                    placeholder="••••••••"
                    required
            >
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            Zaloguj się
          </button>
        </form>

        <div class="login-info">
          <p>Demo: <strong>admin@szpontowe-seanse.pl</strong> / <strong>admin123</strong></p>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="js/theme-switcher.js"></script>
<script>
    <?php if (!empty($logout_message)): ?>
    alert('✅ <?php echo addslashes($logout_message); ?>');
    <?php endif; ?>
</script>
</body>
</html>