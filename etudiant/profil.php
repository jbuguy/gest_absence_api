<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

if(isset($_GET['utilisateur_id'])) {
    $u_id = $_GET['utilisateur_id'];

    // On récupère l'utilisateur, son profil étudiant et le nom de sa classe
    $query = "SELECT u.nom, u.prenom, u.email, c.nom as classe_nom, c.niveau 
              FROM utilisateurs u
              JOIN etudiants e ON u.id = e.utilisateur_id
              JOIN classes c ON e.classe_id = c.id
              WHERE u.id = :u_id";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':u_id' => $u_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if($user) {
        echo json_encode(["success" => 1, "data" => $user]);
    } else {
        echo json_encode(["success" => 0, "message" => "Profil non trouvé"]);
    }
}
?>