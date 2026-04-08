<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$db = getConnection();

function getEtudiantsByClasses($db)
{
    if (!isset($_GET['id'])) {
        echo json_encode(["success" => 0, "message" => "classe_id manquant"]);
        return;
    }

    $classe_id = intval($_GET['id']);
    $query = "SELECT  * FROM etudiants as e
              JOIN utilisateurs as u
              ON e.utilisateur_id = u.id AND e.classe_id= ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $classe_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $seances = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    echo json_encode(["success" => 1, "data" => $seances]);
}

getEtudiantsByClasses($db);
