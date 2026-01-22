<?php

session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Brak uprawnień administratora.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_recenzji = isset($_POST['id_recenzji']) ? (int)$_POST['id_recenzji'] : 0;
    $id_filmu = isset($_POST['id_filmu']) ? (int)$_POST['id_filmu'] : 0;

    if ($id_recenzji <= 0 || $id_filmu <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Błędne ID.']);
        exit;
    }

    try {
        $database = new Database();
        $db = $database->connect();

        $query = "DELETE FROM Recenzje WHERE id_recenzji = :id_recenzji";
        $stmt = $db->prepare($query);
        $stmt->execute([':id_recenzji' => $id_recenzji]);

        $avg_query = "SELECT AVG(ocena_gwiazdki) as srednia FROM Recenzje WHERE id_filmu = :id_filmu";
        $avg_stmt = $db->prepare($avg_query);
        $avg_stmt->execute([':id_filmu' => $id_filmu]);
        $avg_result = $avg_stmt->fetch();

        $nowa_srednia = $avg_result['srednia'] ? round($avg_result['srednia'], 2) : 0;

        $update_query = "UPDATE Filmy SET srednia_ocena = :srednia WHERE id_filmu = :id_filmu";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->execute([
            ':srednia' => $nowa_srednia,
            ':id_filmu' => $id_filmu
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Recenzja usunięta.']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Błąd: ' . $e->getMessage()]);
    }
}