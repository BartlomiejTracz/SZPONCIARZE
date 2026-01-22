<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $database = new Database();
        $db = $database->connect();

        $id_filmu = isset($_POST['id_filmu']) ? (int)$_POST['id_filmu'] : 0;
        $ocena = isset($_POST['ocena']) ? (int)$_POST['ocena'] : 0;
        $tresc = isset($_POST['tresc']) ? trim($_POST['tresc']) : '';

        if ($id_filmu <= 0 || $ocena <= 0 || empty($tresc)) {
            echo json_encode(['status' => 'error', 'message' => 'Wypełnij wszystkie pola i oceń film!']);
            exit;
        }

        $query = "INSERT INTO Recenzje (id_filmu, ocena_gwiazdki, tresc_recenzji, data_dodania) 
                  VALUES (:id_filmu, :ocena, :tresc, datetime('now'))";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':id_filmu' => $id_filmu,
            ':ocena' => $ocena,
            ':tresc' => $tresc
        ]);

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

        echo json_encode(['status' => 'success', 'message' => 'Recenzja została dodana!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Błąd bazy danych: ' . $e->getMessage()]);
    }
}
