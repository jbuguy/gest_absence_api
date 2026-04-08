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
    if (empty($data->seance_id) || empty($data->absences) || !is_array($data->absences)) {
        echo json_encode(["success" => 0, "message" => "Données incomplètes (besoin de seance_id et d'une liste d'absences)"]);
        return;
    }

    $seance_id = $data->seance_id;
    $query = "INSERT INTO absences (etudiant_id, seance_id, statut) VALUES (?, ?, ?)";
    $stmt = $db->prepare($query);

    $successCount = 0;
    $errorCount = 0;
    $errors = [];

    foreach ($data->absences as $absence) {
        if (empty($absence->etudiant_id) || !isset($absence->statut)) {
            $errorCount++;
            $errors[] = "Données manquantes pour un étudiant";
            continue;
        }

        $etudiant_id = $absence->etudiant_id;
        $statut = $absence->statut;
        $stmt->bind_param('iis', $etudiant_id, $seance_id, $statut);

        if ($stmt->execute()) {
            $successCount++;
        } else {
            $errorCount++;
            $errors[] = "Erreur pour l'étudiant {$etudiant_id}: " . $stmt->error;
        }
    }

    echo json_encode([
        "success" => ($errorCount === 0) ? 1 : 0,
        "message" => "Absences enregistrées: {$successCount} réussies, {$errorCount} échouées",
        "successCount" => $successCount,
        "errorCount" => $errorCount,
        "errors" => $errors
    ]);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getAbsenceStudents($db);
        break;
    case 'POST':
        createAbsence($db, $data);
        break;
}
