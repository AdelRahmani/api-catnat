<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';


$cp = get_qs('code_postal');

if ($cp !== null && $cp !== '') {
    $sql = "SELECT id, LPAD(insee,5,'0') AS insee, LPAD(code_postal,5,'0') AS code_postal
            FROM codes WHERE code_postal = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) json_error("Erreur préparation requête", 500, ["mysql" => $mysqli->error]);
    $stmt->bind_param("s", $cp);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_all(MYSQLI_ASSOC);
    json_ok($data);
} else {
    $sql = "SELECT id, LPAD(insee,5,'0') AS insee, LPAD(code_postal,5,'0') AS code_postal
            FROM codes LIMIT 50";
    $res = $mysqli->query($sql);
    if (!$res) json_error("Erreur exécution requête", 500, ["mysql" => $mysqli->error]);
    $data = $res->fetch_all(MYSQLI_ASSOC);
    json_ok($data);
}
