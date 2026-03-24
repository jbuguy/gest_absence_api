<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$db = getConnection();

function getProfilEtudiant($db)
{
    if (!isset($_GET['utilisateur_id'])) {
        echo json_encode(["success" => 0, "message" => "utilisateur_id manquant"]);
        return;
    }

    $u_id = intval($_GET['utilisateur_id']);
    $query = "SELECT u.nom, u.prenom, u.email, c.nom as classe_nom, c.niveau 
              FROM utilisateurs u
              JOIN etudiants e ON u.id = e.utilisateur_id
              JOIN classes c ON e.classe_id = c.id
              WHERE u.id = ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $u_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result ? $result->fetch_assoc() : null;

    if ($user) {
        echo json_encode(["success" => 1, "data" => $user]);
    } else {
        echo json_encode(["success" => 0, "message" => "Profil non trouvé"]);
    }
}

getProfilEtudiant($db);
?>