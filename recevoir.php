<?php
// Décode un payload hexadécimal en floats température et humidité
function decodePayload($payload) {
    $tempHex = substr($payload, 0, 8);
    $humHex  = substr($payload, 8, 8);

    $temperature = unpack('f', hex2bin($tempHex))[1];
    $humidity    = unpack('f', hex2bin($humHex))[1];

    return [$temperature, $humidity];
}

// Vérifie que le numéro de séquence est correct
function verifierSequence($device, $nouveauSeq)
{
    $host = 'gi50x.myd.infomaniak.com';
    $db   = 'gi50x_IoT';
    $user = 'gi50x_salours';
    $pass = 'Atelier IoT 1';
    $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT seqNum FROM mesures WHERE device = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$device]);
        $dernier = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dernier) {
            return true; // Aucun précédent
        }

        $dernierSeq = (int)$dernier['seqNum'];
        $nouveauSeq = (int)$nouveauSeq;

        if ($nouveauSeq === $dernierSeq + 1) {
            return true;
        } else {
            error_log("Attention : Message(s) manqué(s) pour $device entre $dernierSeq et $nouveauSeq.");
            return false;
        }

    } catch (PDOException $e) {
        error_log("Erreur BDD séquence : " . $e->getMessage());
        return true;
    }
}

// Insert les données en base
function insererDonnees($device, $date, $temperature, $humidity, $seqNum)
{
    $host = 'gi50x.myd.infomaniak.com';
    $db   = 'gi50x_IoT';
    $user = 'gi50x_salours';
    $pass = 'Atelier IoT 1';
    $dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("
            INSERT INTO mesures (device, time, temperature, humidity, seqNum) 
            VALUES (?, ?, ?, ?, ?)
        ");

        return $stmt->execute([$device, $date, $temperature, $humidity, $seqNum]);
    } catch (PDOException $e) {
        error_log("Erreur BDD insertion : " . $e->getMessage());
        return false;
    }
}

// -----------------------------------------------------------
// Script principal
// -----------------------------------------------------------
$data = file_get_contents('php://input');

if ($data) {
    $json = json_decode($data, true);

    if ($json) {
        $device  = $json['device'] ?? null;
        $time    = $json['time'] ?? null;
        $payload = $json['data'] ?? null;
        $seqNum  = $json['seqNum'] ?? null;

        $date = date("Y-m-d H:i:s", $time);

        if ($device && $time && $payload && $seqNum !== null) {
            [$temperature, $humidity] = decodePayload($payload);

            if (verifierSequence($device, $seqNum)) {
                if (insererDonnees($device, $date, $temperature, $humidity, $seqNum)) {
                    echo "Données insérées avec succès";
                } else {
                    http_response_code(500);
                    echo "Erreur lors de l'insertion";
                }
            } else {
                http_response_code(206);
                echo "Attention : Messages manqués détectés.";
            }
        } else {
            http_response_code(400);
            echo "Données incomplètes";
        }
    } else {
        http_response_code(400);
        echo "JSON invalide";
    }
} else {
    http_response_code(400);
    echo "Aucune donnée reçue";
}
?>
