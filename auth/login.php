<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/database.php'; // Assure-toi que ce fichier existe

// Connexion à la base (à adapter selon ton fichier database.php)
$database = new Database();
$db = $database->getConnection();

// Récupérer les données envoyées en POST
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)){
    
    $query = "SELECT id, nom, prenom, role FROM utilisateurs 
              WHERE email = :email AND password = :password LIMIT 0,1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":password", $data->password);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        // Succès : on renvoie les infos de l'utilisateur 
        echo json_encode([
            "success" => 1,
            "user" => $row
        ]);
    } else {
        // Échec : identifiants incorrects [cite: 168]
        echo json_encode(["success" => 0, "message" => "Email ou mot de passe incorrect."]);
    }
} else {
    echo json_encode(["success" => 0, "message" => "Données incomplètes."]);
}
?>