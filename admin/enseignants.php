<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS,DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';
$db = getConnection();
$data = json_decode(file_get_contents("php://input"));

function getEnseignants($db)
{
    $query = "SELECT u.id as user_id, ens.id as id, u.nom, u.prenom, u.email, ens.specialite 
              FROM enseignants ens 
              JOIN utilisateurs u ON ens.utilisateur_id = u.id";
    $result = $db->query($query);
    $enseignants = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $enseignants = array_map(function ($row) {
        $row['user_id'] = (int) $row['user_id'];
        $row['id'] = (int) $row['id'];
        return $row;
    }, $enseignants);
    echo json_encode(["success" => 1, "data" => $enseignants]);
}

function createEnseignant($db, $data)
{
    if (empty($data->nom) || empty($data->prenom) || empty($data->email) || empty($data->password) || empty($data->specialite)) {
        echo json_encode(["success" => 0, "message" => "Données incomplètes. Assurez-vous d'envoyer 'specialite'."]);
        return;
    }

    try {
        $db->begin_transaction();

        $queryUser = "INSERT INTO utilisateurs (nom, prenom, email, password, role) VALUES (?, ?, ?, ?, 'enseignant')";
        $stmtUser = $db->prepare($queryUser);
        $stmtUser->bind_param('ssss', $data->nom, $data->prenom, $data->email, $data->password);
        $stmtUser->execute();

        $userId = $db->insert_id;

        $queryEns = "INSERT INTO enseignants (utilisateur_id, specialite) VALUES (?, ?)";
        $stmtEns = $db->prepare($queryEns);
        $stmtEns->bind_param('is', $userId, $data->specialite);
        $stmtEns->execute();

        $db->commit();
        echo json_encode(["success" => 1, "message" => "Enseignant ajouté avec succès"]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
    }
}

function updateEnseignant($db, $data)
{
    if (empty($data->utilisateur_id) || empty($data->nom) || empty($data->prenom) || empty($data->matiere_id)) {
        echo json_encode(["success" => 0, "message" => "Données incomplètes pour mise à jour"]);
        return;
    }

    try {
        $db->begin_transaction();

        $qUser = "UPDATE utilisateurs SET nom = ?, prenom = ? WHERE id = ?";
        $stUser = $db->prepare($qUser);
        $stUser->bind_param('ssi', $data->nom, $data->prenom, $data->utilisateur_id);
        $stUser->execute();

        $qEns = "UPDATE enseignants SET matiere_id = ? WHERE utilisateur_id = ?";
        $stEns = $db->prepare($qEns);
        $stEns->bind_param('ii', $data->matiere_id, $data->utilisateur_id);
        $stEns->execute();

        $db->commit();
        echo json_encode(["success" => 1, "message" => "Enseignant mis à jour"]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
    }
}

function deleteEnseignant($db, $data)
{
    if (empty($_GET["id"])) {
        echo json_encode(["success" => 0, "message" => "ID utilisateur d'enseignant manquant"]);
        return;
    }

    try {
        $db->begin_transaction();

        $qEns = "DELETE FROM enseignants WHERE utilisateur_id = ?";
        $stEns = $db->prepare($qEns);
        $stEns->bind_param('i', $_GET["id"]);
        $stEns->execute();

        $qUser = "DELETE FROM utilisateurs WHERE id = ?";
        $stUser = $db->prepare($qUser);
        $stUser->bind_param('i', $_GET["id"]);
        $stUser->execute();

        $db->commit();
        echo json_encode(["success" => 1, "message" => "Enseignant supprimé"]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(["success" => 0, "message" => "Erreur : " . $e->getMessage()]);
    }
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        getEnseignants($db);
        break;
    case 'POST':
        createEnseignant($db, $data);
        break;
    case 'PUT':
        updateEnseignant($db, $data);
        break;
    case 'DELETE':
        deleteEnseignant($db, $data);
        break;
}