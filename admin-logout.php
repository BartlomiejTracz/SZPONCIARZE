<?php
session_start();
$redirect_to = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';

// Jeśli wylogowuje się z panelu admina, przekieruj do logowania
$admin_pages = ['admin-dashboard.php', 'admin-movies.php', 'admin-categories.php'];
$is_admin_page = false;

foreach ($admin_pages as $page) {
    if (strpos($redirect_to, $page) !== false) {
        $is_admin_page = true;
        break;
    }
}

$_SESSION = array();

session_start();
$_SESSION['logout_message'] = 'Zostałeś pomyślnie wylogowany';


if ($is_admin_page) {
    header('Location: admin-login.php');
} else {
    header('Location: ' . $redirect_to);
}
exit;
?>