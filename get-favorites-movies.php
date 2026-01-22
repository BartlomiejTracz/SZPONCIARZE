<?php
error_reporting(0);
header('Content-Type: application/json');

require_once 'config/database.php';

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!isset($data['ids']) || empty($data['ids'])) {
        echo json_encode([]);
        exit;
    }

    $ids = array_map('intval', $data['ids']);

    $database = new Database();
    $db = $database->connect();

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $query = "SELECT * FROM filmy WHERE id_filmu IN ($placeholders)";
    $stmt = $db->prepare($query);
    $stmt->execute($ids);

    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (ob_get_length()) ob_clean();
    echo json_encode($movies);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
exit;