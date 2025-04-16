<?php
$host = 'gi50x.myd.infomaniak.com';
$db   = 'gi50x_IoT';
$user = 'gi50x_salours';
$pass = 'Atelier IoT 1';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id, device, time, temperature, humidity FROM mesures ORDER BY id DESC LIMIT 50");
    $mesures = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($mesures);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur de connexion']);
}
?>
