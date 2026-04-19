<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS,DELETE");

include_once '../config/database.php';

$db = getConnection();
$data = json_decode(file_get_contents("php://input"));

function getMatieres($db)
{
    $query = "SELECT id, nom FROM matieres";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $matieres = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $matieres = array_map(function ($row) {
        $row['id'] = (int) $row['id'];
        return $row;
    }, $matieres);

    echo json_encode(["success" => 1, "data" => $matieres]);
}

function createMatiere($db, $data)
{
    if (empty($data->nom)) {
        echo json_encode(["success" => 0, "message" => "Nom de matière manquant"]);
        return;
    }

    $query = "INSERT INTO matieres (nom) VALUES (?)";
    $stmt = $db->prepare($query);
    $stmt->bind_param('s', $data->nom);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Matière créée"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur SQL : " . $stmt->error]);
    }
}

function updateMatiere($db, $data)
{
    if (empty($data->id) || empty($data->nom)) {
        echo json_encode(["success" => 0, "message" => "Données incomplètes"]);
        return;
    }

    $query = "UPDATE matieres SET nom = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('si', $data->nom, $data->id);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Matière mise à jour"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur SQL : " . $stmt->error]);
    }
}

function deleteMatiere($db, $data)
{
    if (empty($_GET["id"])) {
        echo json_encode(["success" => 0, "message" => "ID de matière manquant"]);
        return;
    }

    $query = "DELETE FROM matieres WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->bind_param('i', $_GET["id"]);

    if ($stmt->execute()) {
        echo json_encode(["success" => 1, "message" => "Matière supprimée"]);
    } else {
        echo json_encode(["success" => 0, "message" => "Erreur SQL : " . $stmt->error]);
    }
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getMatieres($db);
        break;
    case 'POST':
        createMatiere($db, $data);
        break;
    case 'PUT':
        updateMatiere($db, $data);
        break;
    case 'DELETE':
        deleteMatiere($db, $data);
        break;
}
