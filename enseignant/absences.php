<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

include_once '../config/database.php';
$db = getConnection();
$data = json_decode(file_get_contents("php://input"));

function getAbsenceStudents($db)
{
    if (!isset($_GET['classe_id'])) {
        echo json_encode(["success" => 0, "message" => "Paramètre classe_id manquant"]);
        return;
    }

    $classe_id = intval($_GET['classe_id']);
    $query = "SELECT u.id as utilisateur_id, e.id as etudiant_id, u.nom, u.prenom 
              FROM etudiants e 
              JOIN utilisateurs u ON e.utilisateur_id = u.id 
              WHERE e.classe_id = ?";

    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $classe_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $students = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    echo json_encode(["success" => 1, "data" => $students]);
}

function createAbsence($db, $data)
{
    if (empty($data->etudiant_id) || empty($data->seance_id) || !isset($data->statut)) {
        echo json_encode(["success" => 0, "message" => "Données incomplètes (besoin de etudiant_id, seance_id et statut)"]);
        return;
    }

    $query = "INSERT INTO absences (etudiant_id, seance_id, statut) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param('iis', $data->etudiant_id, $data->seance_id, $data->statut);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Absence enregistrée dans la base"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur SQL : " . $stmt->error]);
    }
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getAbsenceStudents($db);
        break;
    case 'POST':
        createAbsence($db, $data);
        break;
}
?>