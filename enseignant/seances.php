<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$db = getConnection();

function getEnseignantSeances($db)
{
    if (!isset($_GET['utilisateur_id'])) {
        echo json_encode(["success" => 0, "message" => "utilisateur_id manquant"]);
        return;
    }

    $u_id = intval($_GET['utilisateur_id']);
    $query = "SELECT s.*, c.nom as classe_nom, m.nom as matiere_nom 
              FROM seances s
              JOIN enseignants e ON s.enseignant_id = e.id
              JOIN classes c ON s.classe_id = c.id
              JOIN matieres m ON s.matiere_id = m.id
              WHERE e.utilisateur_id = ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $u_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $seances = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    echo json_encode(["success" => 1, "data" => $seances]);
}

getEnseignantSeances($db);
?>