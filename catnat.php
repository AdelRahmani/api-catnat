<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';


$insee = get_qs('insee');

if ($insee !== null && $insee !== '') {
    $sql = "SELECT id, insee, nom_commune, debut_evenement, fin_evenement, arrete_du, parution_au_jo, nom_peril, code_peril, franchise, code_nor, decision
            FROM catnat WHERE insee = ?";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) json_error("Erreur préparation requête", 500, ["mysql" => $mysqli->error]);
    $stmt->bind_param("s", $insee);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_all(MYSQLI_ASSOC);
    json_ok($data);
} else {
    $sql = "SELECT id, insee, nom_commune, debut_evenement, fin_evenement, arrete_du, parution_au_jo, nom_peril, code_peril, franchise, code_nor, decision
            FROM catnat ORDER BY arrete_du DESC LIMIT 50";
    $res = $mysqli->query($sql);
    if (!$res) json_error("Erreur exécution requête", 500, ["mysql" => $mysqli->error]);
    $data = $res->fetch_all(MYSQLI_ASSOC);
    json_ok($data);
}
