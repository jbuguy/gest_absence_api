<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if(isset($_GET['utilisateur_id'])) {
    $u_id = $_GET['utilisateur_id'];
    // Version simplifiée qui cherche l'enseignant lié à l'utilisateur
    $query = "SELECT s.*, c.nom as classe_nom, m.nom as matiere_nom 
              FROM seances s
              JOIN enseignants e ON s.enseignant_id = e.id
              JOIN classes c ON s.classe_id = c.id
              JOIN matieres m ON s.matiere_id = m.id
              WHERE e.utilisateur_id = :u_id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':u_id' => $u_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => 1, "data" => $result]);
}
?>