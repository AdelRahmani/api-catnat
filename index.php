<?php
require_once __DIR__ . '/helpers.php';

json_headers();

// Route simple via ?r=...
$route = isset($_GET['r']) ? trim($_GET['r']) : '';

switch ($route) {
    case 'catnat':
        require __DIR__ . '/catnat.php';
        break;
    case 'codes':
        require __DIR__ . '/codes.php';
        break;
    case 'ppr':
        require __DIR__ . '/ppr.php';
        break;
    case 'recherche':
        require __DIR__ . '/recherche.php';
        break;
    case '':
        json_ok([
            "message" => "Bienvenue sur l'API CatNat",
            "endpoints" => [
                "/api/index.php?r=codes&code_postal=75019",
                "/api/index.php?r=catnat&insee=75119",
                "/api/index.php?r=ppr&code_insee=75119",
                "/api/index.php?r=recherche&code_postal=75019"
            ]
        ]);
        break;
    default:
        json_error("Endpoint inconnu. Utilise r=codes|catnat|ppr|recherche", 404);
}
