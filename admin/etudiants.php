<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        // Récupère la liste des étudiants avec le nom de leur classe [cite: 152, 252]
        $query = "SELECT u.id as utilisateur_id, e.id as etudiant_id, u.nom, u.prenom, u.email, c.nom as classe_nom 
                  FROM etudiants e 
                  JOIN utilisateurs u ON e.utilisateur_id = u.id 
                  JOIN classes c ON e.classe_id = c.id";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(["success" => 1, "data" => $etudiants]);
        break;

    case 'POST':
        // Ajout d'un nouvel étudiant (requiert 2 insertions) [cite: 152, 254]
        if(!empty($data->nom) && !empty($data->prenom) && !empty($data->email) && !empty($data->password) && !empty($data->classe_id)) {
            try {
                $db->beginTransaction();

                // 1. Insertion dans la table utilisateurs [cite: 39, 54]
                $queryUser = "INSERT INTO utilisateurs (nom, prenom, email, password, role) VALUES (:nom, :prenom, :email, :password, 'etudiant')";
                $stmtUser = $db->prepare($queryUser);
                $stmtUser->execute([
                    ':nom' => $data->nom,
                    ':prenom' => $data->prenom,
                    ':email' => $data->email,
                    ':password' => $data->password // Stocké en clair comme demandé [cite: 131]
                ]);
                $userId = $db->lastInsertId();

                // 2. Insertion dans la table etudiants [cite: 66, 70]
                $queryEtu = "INSERT INTO etudiants (utilisateur_id, classe_id) VALUES (:u_id, :c_id)";
                $stmtEtu = $db->prepare($queryEtu);
                $stmtEtu->execute([
                    ':u_id' => $userId,
                    ':c_id' => $data->classe_id
                ]);

                $db->commit();
                echo json_encode(["success" => 1, "message" => "Étudiant ajouté avec succès"]);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
            }
        } else {
            echo json_encode(["success" => 0, "message" => "Données incomplètes"]);
        }
        break;

    case 'PUT':
        // Modification d'un étudiant existant [cite: 152, 256]
        if(!empty($data->utilisateur_id) && !empty($data->nom) && !empty($data->prenom) && !empty($data->classe_id)) {
            try {
                $db->beginTransaction();

                // Mise à jour des infos de base [cite: 39]
                $qUser = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom WHERE id = :id";
                $stUser = $db->prepare($qUser);
                $stUser->execute([
                    ':nom' => $data->nom,
                    ':prenom' => $data->prenom,
                    ':id' => $data->utilisateur_id
                ]);

                // Mise à jour de la classe [cite: 66]
                $qEtu = "UPDATE etudiants SET classe_id = :c_id WHERE utilisateur_id = :u_id";
                $stEtu = $db->prepare($qEtu);
                $stEtu->execute([
                    ':c_id' => $data->classe_id,
                    ':u_id' => $data->utilisateur_id
                ]);

                $db->commit();
                echo json_encode(["success" => 1, "message" => "Étudiant mis à jour"]);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
            }
        }
        break;
}
?>