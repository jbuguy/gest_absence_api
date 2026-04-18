<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include_once '../config/database.php';

$db = getConnection();
$data = json_decode(file_get_contents("php://input"));

function getSeances($db)
{
    $query = "SELECT s.*, u.nom as prof_nom, c.nom as classe_nom, m.nom as matiere_nom 
              FROM seances s
              JOIN enseignants e ON s.enseignant_id = e.id
              JOIN utilisateurs u ON e.utilisateur_id = u.id
              JOIN classes c ON s.classe_id = c.id
              JOIN matieres m ON s.matiere_id = m.id";
    $result = $db->query($query);
    $seances = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $seances = array_map(function ($row) {
        $row['id'] = (int) $row['id'];
        $row["classe_id"] = (int) $row["classe_id"];
        $row["matiere_id"] = (int) $row["matiere_id"];
        $row["enseignant_id"] = (int) $row["enseignant_id"];
        return $row;
    }, $seances);
    echo json_encode(["success" => 1, "data" => $seances]);
}

function createSeance($db, $data)
{
    if (empty($data->enseignant_id) || empty($data->classe_id) || empty($data->matiere_id)) {
        echo json_encode(["success" => 0, "message" => "Données incomplètes"]);
        return;
    }

    $query = "INSERT INTO seances (enseignant_id, classe_id, matiere_id, date_seance, heure_debut, heure_fin) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param('iiisss', $data->enseignant_id, $data->classe_id, $data->matiere_id, $data->date_seance, $data->heure_debut, $data->heure_fin);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Séance planifiée"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur SQL : $stmt->error"]);
    }
}

function updateSeance($db, $data)
{
    if (empty($data->id) || empty($data->enseignant_id) || empty($data->classe_id) || empty($data->matiere_id)) {
        echo json_encode(["success" => 0, "message" => "Données incomplètes"]);
        return;
    }

    $query = "UPDATE seances SET enseignant_id = ?, classe_id = ?, matiere_id = ?, date_seance = ?, heure_debut = ?, heure_fin = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('iiisssi', $data->enseignant_id, $data->classe_id, $data->matiere_id, $data->date_seance, $data->heure_debut, $data->heure_fin, $data->id);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Séance mise à jour"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur SQL : $stmt->error"]);
    }
}

function deleteSeance($db, $data)
{
    if (empty($data->id)) {
        echo json_encode(["success" => 0, "message" => "ID de séance manquant"]);
        return;
    }

    $query = "DELETE FROM seances WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $data->id);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Séance supprimée"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur SQL : $stmt->error"]);
    }
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getSeances($db);
        break;
    case 'POST':
        createSeance($db, $data);
        break;
    case 'PUT':
        updateSeance($db, $data);
        break;
    case 'DELETE':
        deleteSeance($db, $data);
        break;
}
