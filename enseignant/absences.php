<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

include_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        // Récupérer les étudiants d'une classe pour faire l'appel
        if(isset($_GET['classe_id'])) {
            $classe_id = $_GET['classe_id'];
            $query = "SELECT u.id as utilisateur_id, e.id as etudiant_id, u.nom, u.prenom 
                      FROM etudiants e 
                      JOIN utilisateurs u ON e.utilisateur_id = u.id 
                      WHERE e.classe_id = :c_id";
            
            $stmt = $db->prepare($query);
            $stmt->bindParam(':c_id', $classe_id);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(["success" => 1, "data" => $result]);
        }
        break;

    case 'POST':
        // Marquer une absence selon TA structure : seance_id, etudiant_id, statut
        if(!empty($data->etudiant_id) && !empty($data->seance_id) && isset($data->statut)) {
            
            // Requête adaptée : pas de colonne 'date' ici
            $query = "INSERT INTO absences (etudiant_id, seance_id, statut) 
                      VALUES (:e_id, :s_id, :statut)";
            
            $stmt = $db->prepare($query);
            
            try {
                if($stmt->execute([
                    ':e_id' => $data->etudiant_id,
                    ':s_id' => $data->seance_id,
                    ':statut' => $data->statut
                ])) {
                    echo json_encode(["success" => 1, "message" => "Absence enregistrée dans la base"]);
                }
            } catch (PDOException $e) {
                echo json_encode(["success" => 0, "message" => "Erreur SQL : " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["success" => 0, "message" => "Données incomplètes (besoin de etudiant_id, seance_id et statut)"]);
        }
        break;
}
?>