<?php
require __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';


$cp = get_qs('code_postal');
if ($cp === null || $cp === '') {
    json_error("Merci de renseigner un code_postal. Ex: /api/index.php?r=recherche&code_postal=75019", 422);
}

// 1) COMMUNES pour ce CP
$sqlCodes = "SELECT LPAD(insee,5,'0') AS insee, LPAD(code_postal,5,'0') AS code_postal
             FROM codes WHERE code_postal = ?";
$stmtCodes = $mysqli->prepare($sqlCodes);
if (!$stmtCodes) json_error("Erreur préparation requête codes", 500, ["mysql" => $mysqli->error]);
$stmtCodes->bind_param("s", $cp);
$stmtCodes->execute();
$resCodes = $stmtCodes->get_result();
$communes = $resCodes->fetch_all(MYSQLI_ASSOC);

if (empty($communes)) {
    json_ok(["message" => "Aucune commune trouvée pour ce code postal", "code_postal" => $cp], 200);
}

$data = [];

$sqlCatNat = "SELECT id, insee, nom_commune, debut_evenement, fin_evenement, arrete_du, parution_au_jo, nom_peril, code_peril, franchise, code_nor, decision
              FROM catnat WHERE insee = ?";
$stmtCatNat = $mysqli->prepare($sqlCatNat);
if (!$stmtCatNat) json_error("Erreur préparation requête catnat", 500, ["mysql" => $mysqli->error]);

$sqlPpr = "SELECT id, cod_nat_ppr, lib_bassin_risques, num_risque, lib_risque, code_insee, lib_commune, cod_ppr, dat_prescription, dat_approbation, dat_modification, dat_annulation
           FROM ppr WHERE code_insee = ?";
$stmtPpr = $mysqli->prepare($sqlPpr);
if (!$stmtPpr) json_error("Erreur préparation requête ppr", 500, ["mysql" => $mysqli->error]);

foreach ($communes as $c) {
    $insee = $c['insee'];

    // CatNat
    $stmtCatNat->bind_param("s", $insee);
    $stmtCatNat->execute();
    $resCat = $stmtCatNat->get_result();
    $catnat = $resCat->fetch_all(MYSQLI_ASSOC);

    // PPR
    $stmtPpr->bind_param("s", $insee);
    $stmtPpr->execute();
    $resPpr = $stmtPpr->get_result();
    $ppr = $resPpr->fetch_all(MYSQLI_ASSOC);

    $data[] = [
        "code_postal" => $c['code_postal'],
        "insee"       => $insee,
        "catnat"      => $catnat,
        "ppr"         => $ppr
    ];
}

json_ok($data);
