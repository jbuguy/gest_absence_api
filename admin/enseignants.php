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
    // On récupère le prof et sa spécialité directement depuis la table enseignants
    $query = "SELECT u.id as utilisateur_id, ens.id as enseignant_id, u.nom, u.prenom, u.email, ens.specialite 
              FROM enseignants ens 
              JOIN utilisateurs u ON ens.utilisateur_id = u.id";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $enseignants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(["success" => 1, "data" => $enseignants]);
    break;

    case 'POST':
    // On vérifie si on a bien reçu "specialite" au lieu de "matiere_id"
    if(!empty($data->nom) && !empty($data->prenom) && !empty($data->email) && !empty($data->password) && !empty($data->specialite)) {
        try {
            $db->beginTransaction();

            // 1. Créer l'utilisateur (rôle enseignant)
            $queryUser = "INSERT INTO utilisateurs (nom, prenom, email, password, role) VALUES (:nom, :prenom, :email, :password, 'enseignant')";
            $stmtUser = $db->prepare($queryUser);
            $stmtUser->execute([
                ':nom' => $data->nom,
                ':prenom' => $data->prenom,
                ':email' => $data->email,
                ':password' => $data->password
            ]);
            $userId = $db->lastInsertId();

            // 2. Créer l'entrée enseignant avec la colonne "specialite"
            $queryEns = "INSERT INTO enseignants (utilisateur_id, specialite) VALUES (:u_id, :spec)";
            $stmtEns = $db->prepare($queryEns);
            $stmtEns->execute([
                ':u_id' => $userId,
                ':spec' => $data->specialite // On utilise la chaîne de caractères ici
            ]);

            $db->commit();
            echo json_encode(["success" => 1, "message" => "Enseignant ajouté avec succès"]);
        } catch (Exception $e) {
            $db->rollBack();
            echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["success" => 0, "message" => "Données incomplètes. Assurez-vous d'envoyer 'specialite'."]);
    }
    break;

    case 'PUT':
        // Modifier un enseignant
        if(!empty($data->utilisateur_id) && !empty($data->nom) && !empty($data->prenom) && !empty($data->matiere_id)) {
            try {
                $db->beginTransaction();

                $qUser = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom WHERE id = :id";
                $stUser = $db->prepare($qUser);
                $stUser->execute([
                    ':nom' => $data->nom,
                    ':prenom' => $data->prenom,
                    ':id' => $data->utilisateur_id
                ]);

                $qEns = "UPDATE enseignants SET matiere_id = :m_id WHERE utilisateur_id = :u_id";
                $stEns = $db->prepare($qEns);
                $stEns->execute([
                    ':m_id' => $data->matiere_id,
                    ':u_id' => $data->utilisateur_id
                ]);

                $db->commit();
                echo json_encode(["success" => 1, "message" => "Enseignant mis à jour"]);
            } catch (Exception $e) {
                $db->rollBack();
                echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
            }
        }
        break;
}
?>