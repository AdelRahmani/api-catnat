<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

json_headers();

// 🔹 Fonction pour exécuter une requête SQL en sécurité
function safe_query(mysqli $conn, string $sql, array $params = [], string $types = "")
{
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            json_error("Erreur SQL lors de la préparation de la requête", 500, ["sql_error" => $conn->error]);
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            json_error("Erreur lors de l'exécution SQL", 500, ["sql_error" => $stmt->error]);
        }

        $res = $stmt->get_result();
        $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $data;
    } catch (Throwable $e) {
        json_error("Erreur inattendue", 500, ["exception" => $e->getMessage()]);
    }
}

// 🔹 Récupération du code postal depuis l'URL
$cp = get_qs('code_postal');
if ($cp === null || $cp === '') {
    json_error("Merci de renseigner un code_postal. Exemple : /api/index.php?r=recherche&code_postal=75019", 422);
}

// 1️⃣ Recherche des communes correspondant à ce code postal
$sqlCodes = "SELECT LPAD(insee,5,'0') AS insee, LPAD(code_postal,5,'0') AS code_postal
             FROM codes WHERE code_postal = ?";
$communes = safe_query($mysqli, $sqlCodes, [$cp], "s");

if (empty($communes)) {
    json_ok(["message" => "Aucune commune trouvée pour ce code postal", "code_postal" => $cp]);
}

// 2️⃣ Préparation des requêtes CatNat et PPR
$sqlCatnat = "SELECT id, insee, nom_commune, debut_evenement, fin_evenement, 
                     arrete_du, parution_au_jo, nom_peril, code_peril, franchise, code_nor, decision
              FROM catnat WHERE insee = ?";

$sqlPpr = "SELECT id, cod_nat_pprn, lib_bassin_risques, num_risque, lib_risque, 
                  code_insee, lib_commune, cod_ppr, dat_prescription, dat_approbation, dat_modification, dat_annulation
           FROM ppr WHERE code_insee = ?";


$data = [];

// 3️⃣ Pour chaque commune, on récupère les CatNat et PPR associés
foreach ($communes as $c) {
    $insee = $c['insee'];
    $catnat = safe_query($mysqli, $sqlCatnat, [$insee], "s");
    $ppr = safe_query($mysqli, $sqlPpr, [$insee], "s");

    $data[] = [
        "code_postal" => $c['code_postal'],
        "insee" => $insee,
        "catnat" => $catnat,
        "ppr" => $ppr
    ];
}

// 4️⃣ Si aucun résultat global, message clair
if (empty($data)) {
    json_ok(["message" => "Aucune donnée trouvée pour ce code postal", "code_postal" => $cp]);
}

// 5️⃣ Tout est bon → on renvoie la réponse JSON
json_ok($data);
