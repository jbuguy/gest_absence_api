<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"));

switch($method) {
    case 'GET':
        $query = "SELECT s.*, u.nom as prof_nom, c.nom as classe_nom, m.nom as matiere_nom 
                  FROM seances s
                  JOIN enseignants e ON s.enseignant_id = e.id
                  JOIN utilisateurs u ON e.utilisateur_id = u.id
                  JOIN classes c ON s.classe_id = c.id
                  JOIN matieres m ON s.matiere_id = m.id";
        $stmt = $db->prepare($query);
        $stmt->execute();
        echo json_encode(["success" => 1, "data" => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        break;

    case 'POST':
        if(!empty($data->enseignant_id) && !empty($data->classe_id) && !empty($data->matiere_id)) {
            $query = "INSERT INTO seances (enseignant_id, classe_id, matiere_id, date_seance, heure_debut, heure_fin) 
                      VALUES (:e_id, :c_id, :m_id, :date, :h_deb, :h_fin)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':e_id' => $data->enseignant_id,
                ':c_id' => $data->classe_id,
                ':m_id' => $data->matiere_id,
                ':date' => $data->date_seance,
                ':h_deb' => $data->heure_debut,
                ':h_fin' => $data->heure_fin
            ]);
            echo json_encode(["success" => 1, "message" => "Séance planifiée"]);
        }
        break;
}