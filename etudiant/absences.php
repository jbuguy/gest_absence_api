<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if(isset($_GET['utilisateur_id'])) {
    $u_id = $_GET['utilisateur_id'];

    // On cherche les absences liées à cet UTILISATEUR via la table etudiants
    $query = "SELECT s.date_seance, m.nom as matiere_nom, s.heure_debut, a.statut
              FROM absences a
              JOIN seances s ON a.seance_id = s.id
              JOIN matieres m ON s.matiere_id = m.id
              JOIN etudiants e ON a.etudiant_id = e.id
              WHERE e.utilisateur_id = :u_id
              ORDER BY s.date_seance DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':u_id' => $u_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => 1, "data" => $result]);
}
?>