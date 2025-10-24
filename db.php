<?php
// db.php — Connexion MySQLi
$mysqli = @new mysqli("localhost", "adel", "root", "db"); // user=root, mdp vide par défaut sous XAMPP

if ($mysqli->connect_errno) {
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode(["error" => "Erreur de connexion MySQL : " . $mysqli->connect_error], JSON_UNESCAPED_UNICODE);
    exit;
}
$mysqli->set_charset("utf8mb4");
