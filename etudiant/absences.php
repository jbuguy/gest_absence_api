<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$db = getConnection();

function getAbsencesEtudiant($db)
{
    if (!isset($_GET['utilisateur_id'])) {
        echo json_encode(["success" => 0, "message" => "utilisateur_id manquant"]);
        return;
    }

    $u_id = intval($_GET['utilisateur_id']);
    $query = "SELECT s.date_seance, m.nom as matiere_nom, s.heure_debut, a.statut
              FROM absences a
              JOIN seances s ON a.seance_id = s.id
              JOIN matieres m ON s.matiere_id = m.id
              JOIN etudiants e ON a.etudiant_id = e.id
              WHERE e.utilisateur_id = ?
              ORDER BY s.date_seance DESC";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $u_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $absences = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    echo json_encode(["success" => 1, "data" => $absences]);
}

getAbsencesEtudiant($db);
?>